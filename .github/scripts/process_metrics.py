#!/usr/bin/env python3
"""
Process software quality metrics from various PHP analysis tools.
Generates a metrics dashboard with badges, historical data, and trend charts.
"""

import argparse
import json
import os
import sys
from datetime import datetime, timedelta
from pathlib import Path
from typing import Any, Optional

try:
    import defusedxml.ElementTree as ET
except ImportError:
    # Fallback for environments without defusedxml
    # nosemgrep: python.lang.security.use-defused-xml.use-defused-xml
    import xml.etree.ElementTree as ET


# Rating thresholds
THRESHOLDS = {
    'coverage': {'A': 80, 'B': 60, 'C': 40},  # percentage
    'phpstan': {'A': 0, 'B': 10, 'C': 50},    # error count
    'phpcs': {'A': 0, 'B': 20, 'C': 100},     # violation count
    'security': {'A': 0, 'B': 2, 'C': 10},    # issue count
    'complexity': {'A': 5, 'B': 10, 'C': 20}, # average complexity
    'duplication': {'A': 3, 'B': 5, 'C': 10}, # percentage
}

# Badge colors
COLORS = {
    'A': 'brightgreen',
    'B': 'green',
    'C': 'yellow',
    'D': 'red',
}


def calculate_rating(metric: str, value: float) -> str:
    """Calculate A/B/C/D rating based on thresholds."""
    thresholds = THRESHOLDS.get(metric, {})

    # For coverage, higher is better
    if metric == 'coverage':
        if value >= thresholds.get('A', 80):
            return 'A'
        elif value >= thresholds.get('B', 60):
            return 'B'
        elif value >= thresholds.get('C', 40):
            return 'C'
        return 'D'

    # For everything else, lower is better
    if value <= thresholds.get('A', 0):
        return 'A'
    elif value <= thresholds.get('B', 10):
        return 'B'
    elif value <= thresholds.get('C', 50):
        return 'C'
    return 'D'


def generate_badge_url(label: str, value: str, rating: str) -> str:
    """Generate shields.io badge URL."""
    color = COLORS.get(rating, 'lightgrey')
    # URL encode spaces and special characters
    label_encoded = label.replace(' ', '%20').replace('-', '--')
    value_encoded = value.replace(' ', '%20').replace('-', '--').replace('%', '%25')
    return f"https://img.shields.io/badge/{label_encoded}-{value_encoded}-{color}"


def parse_coverage_xml(filepath: str) -> dict:
    """Parse PHPUnit Clover coverage XML."""
    result = {
        'line_coverage': None,
        'branch_coverage': None,
        'lines_covered': 0,
        'lines_total': 0,
        'rating': None,
        'badge_url': None,
    }

    if not os.path.exists(filepath):
        print(f"Warning: Coverage file not found: {filepath}")
        return result

    try:
        tree = ET.parse(filepath)
        root = tree.getroot()

        # Find metrics element (clover format)
        metrics = root.find('.//metrics')
        if metrics is not None:
            statements = int(metrics.get('statements', 0))
            covered_statements = int(metrics.get('coveredstatements', 0))

            if statements > 0:
                result['line_coverage'] = round((covered_statements / statements) * 100, 2)
                result['lines_covered'] = covered_statements
                result['lines_total'] = statements

        # Also check for project-level metrics
        project_metrics = root.find('.//project/metrics')
        if project_metrics is not None and result['line_coverage'] is None:
            statements = int(project_metrics.get('statements', 0))
            covered_statements = int(project_metrics.get('coveredstatements', 0))

            if statements > 0:
                result['line_coverage'] = round((covered_statements / statements) * 100, 2)
                result['lines_covered'] = covered_statements
                result['lines_total'] = statements

        if result['line_coverage'] is not None:
            result['rating'] = calculate_rating('coverage', result['line_coverage'])
            result['badge_url'] = generate_badge_url(
                'coverage',
                f"{result['line_coverage']}%",
                result['rating']
            )
    except ET.ParseError as e:
        print(f"Error parsing coverage XML: {e}")
    except Exception as e:
        print(f"Unexpected error parsing coverage: {e}")

    return result


def parse_phpstan_json(filepath: str) -> dict:
    """Parse PHPStan JSON output."""
    result = {
        'errors': None,
        'files_with_errors': 0,
        'file_breakdown': [],
        'rating': None,
        'badge_url': None,
    }

    if not os.path.exists(filepath):
        print(f"Warning: PHPStan file not found: {filepath}")
        return result

    try:
        with open(filepath, 'r') as f:
            data = json.load(f)

        result['errors'] = data.get('totals', {}).get('errors', 0)

        # Count files with errors
        files = data.get('files', {})
        result['files_with_errors'] = len([f for f, d in files.items() if d.get('errors', 0) > 0])

        # Get top files by error count
        file_errors = [(f, d.get('errors', 0)) for f, d in files.items()]
        file_errors.sort(key=lambda x: x[1], reverse=True)
        result['file_breakdown'] = file_errors[:10]

        if result['errors'] is not None:
            result['rating'] = calculate_rating('phpstan', result['errors'])
            result['badge_url'] = generate_badge_url(
                'PHPStan',
                str(result['errors']) + ' errors' if result['errors'] != 1 else '1 error',
                result['rating']
            )
    except json.JSONDecodeError as e:
        print(f"Error parsing PHPStan JSON: {e}")
    except Exception as e:
        print(f"Unexpected error parsing PHPStan: {e}")

    return result


def parse_phpcs_json(filepath: str) -> dict:
    """Parse PHP CodeSniffer JSON output."""
    result = {
        'violations': None,
        'errors': 0,
        'warnings': 0,
        'files_affected': 0,
        'rating': None,
        'badge_url': None,
    }

    if not os.path.exists(filepath):
        print(f"Warning: PHPCS file not found: {filepath}")
        return result

    try:
        with open(filepath, 'r') as f:
            data = json.load(f)

        totals = data.get('totals', {})
        result['errors'] = totals.get('errors', 0)
        result['warnings'] = totals.get('warnings', 0)
        result['violations'] = result['errors'] + result['warnings']

        # Count files affected
        files = data.get('files', {})
        result['files_affected'] = len([f for f, d in files.items()
                                        if d.get('errors', 0) > 0 or d.get('warnings', 0) > 0])

        if result['violations'] is not None:
            result['rating'] = calculate_rating('phpcs', result['violations'])
            result['badge_url'] = generate_badge_url(
                'code%20style',
                str(result['violations']) + ' issues',
                result['rating']
            )
    except json.JSONDecodeError as e:
        print(f"Error parsing PHPCS JSON: {e}")
    except Exception as e:
        print(f"Unexpected error parsing PHPCS: {e}")

    return result


def parse_security_json(filepath: str) -> dict:
    """Parse security audit JSON output (phpcs-security-audit format)."""
    result = {
        'issues': None,
        'high': 0,
        'medium': 0,
        'low': 0,
        'rating': None,
        'badge_url': None,
    }

    if not os.path.exists(filepath):
        print(f"Warning: Security file not found: {filepath}")
        return result

    try:
        with open(filepath, 'r') as f:
            data = json.load(f)

        totals = data.get('totals', {})
        result['high'] = totals.get('errors', 0)
        result['medium'] = totals.get('warnings', 0)
        result['issues'] = result['high'] + result['medium']

        if result['issues'] is not None:
            result['rating'] = calculate_rating('security', result['issues'])
            result['badge_url'] = generate_badge_url(
                'security',
                str(result['issues']) + ' issues',
                result['rating']
            )
    except json.JSONDecodeError as e:
        print(f"Error parsing security JSON: {e}")
    except Exception as e:
        print(f"Unexpected error parsing security: {e}")

    return result


def parse_phploc_json(filepath: str) -> dict:
    """Parse PHPLOC JSON output."""
    result = {
        'loc': None,
        'lloc': None,
        'classes': None,
        'methods': None,
        'avg_complexity': None,
        'max_complexity': None,
        'rating': None,
        'badge_url': None,
    }

    if not os.path.exists(filepath):
        print(f"Warning: PHPLOC file not found: {filepath}")
        return result

    try:
        with open(filepath, 'r') as f:
            data = json.load(f)

        result['loc'] = data.get('loc', 0)
        result['lloc'] = data.get('lloc', 0)
        result['classes'] = data.get('classes', 0)
        result['methods'] = data.get('methods', 0)

        # Complexity metrics
        result['avg_complexity'] = data.get('ccnByLloc', 0)
        if result['avg_complexity'] == 0:
            # Try alternative key
            methods = data.get('methods', 1)
            ccn = data.get('ccn', 0)
            if methods > 0:
                result['avg_complexity'] = round(ccn / methods, 2)

        result['max_complexity'] = data.get('ccnMax', 0)

        if result['avg_complexity'] is not None:
            result['rating'] = calculate_rating('complexity', result['avg_complexity'])
            result['badge_url'] = generate_badge_url(
                'complexity',
                f"avg {result['avg_complexity']}",
                result['rating']
            )
    except json.JSONDecodeError as e:
        print(f"Error parsing PHPLOC JSON: {e}")
    except Exception as e:
        print(f"Unexpected error parsing PHPLOC: {e}")

    return result


def parse_phpmd_json(filepath: str) -> dict:
    """Parse PHPMD JSON output."""
    result = {
        'violations': None,
        'by_ruleset': {},
        'files_affected': 0,
        'rating': None,
        'badge_url': None,
    }

    if not os.path.exists(filepath):
        print(f"Warning: PHPMD file not found: {filepath}")
        return result

    try:
        with open(filepath, 'r') as f:
            data = json.load(f)

        # PHPMD JSON format may vary
        if isinstance(data, dict):
            files = data.get('files', [])
            total_violations = 0
            rulesets = {}
            affected_files = set()

            for file_info in files:
                violations = file_info.get('violations', [])
                total_violations += len(violations)
                affected_files.add(file_info.get('file', ''))

                for v in violations:
                    ruleset = v.get('ruleSet', 'unknown')
                    rulesets[ruleset] = rulesets.get(ruleset, 0) + 1

            result['violations'] = total_violations
            result['by_ruleset'] = rulesets
            result['files_affected'] = len(affected_files)

            # Calculate rating based on violations (using same thresholds as phpcs)
            if result['violations'] is not None:
                result['rating'] = calculate_rating('phpcs', result['violations'])
                result['badge_url'] = generate_badge_url(
                    'PHPMD',
                    str(result['violations']) + ' issues',
                    result['rating']
                )
    except json.JSONDecodeError as e:
        print(f"Error parsing PHPMD JSON: {e}")
    except Exception as e:
        print(f"Unexpected error parsing PHPMD: {e}")

    return result


def parse_jscpd_json(filepath: str) -> dict:
    """Parse jscpd JSON output."""
    result = {
        'duplicates': None,
        'percentage': None,
        'clones': 0,
        'duplicated_lines': 0,
        'total_lines': 0,
        'by_language': {},
        'rating': None,
        'badge_url': None,
    }

    if not os.path.exists(filepath):
        print(f"Warning: jscpd file not found: {filepath}")
        return result

    try:
        with open(filepath, 'r') as f:
            data = json.load(f)

        statistics = data.get('statistics', {})

        # jscpd has totals either in statistics directly or in statistics.total
        totals = statistics.get('total', statistics)

        # jscpd format
        result['clones'] = totals.get('clones', 0)
        result['duplicated_lines'] = totals.get('duplicatedLines', 0)
        result['total_lines'] = totals.get('lines', 0)

        # Extract per-language breakdown
        formats = statistics.get('formats', {})
        for lang, lang_data in formats.items():
            # Handle both formats: direct stats or nested in 'sources'
            if isinstance(lang_data, dict):
                # Check if it has direct stats or sources
                if 'sources' in lang_data and isinstance(lang_data['sources'], dict):
                    # Real jscpd output: aggregate from sources
                    lang_clones = sum(s.get('clones', 0) for s in lang_data['sources'].values())
                    lang_dup_lines = sum(s.get('duplicatedLines', 0) for s in lang_data['sources'].values())
                    lang_lines = sum(s.get('lines', 0) for s in lang_data['sources'].values())
                    lang_pct = round((lang_dup_lines / lang_lines * 100) if lang_lines > 0 else 0, 2)
                else:
                    # Fixture format: direct stats
                    lang_clones = lang_data.get('clones', 0)
                    lang_dup_lines = lang_data.get('duplicatedLines', 0)
                    lang_lines = lang_data.get('lines', 0)
                    lang_pct = round(lang_data.get('percentage', 0), 2)

                result['by_language'][lang] = {
                    'clones': lang_clones,
                    'duplicated_lines': lang_dup_lines,
                    'lines': lang_lines,
                    'percentage': lang_pct
                }

        # Calculate percentage
        if 'percentage' in totals:
            result['percentage'] = round(totals['percentage'], 2)
        elif result['total_lines'] > 0:
            result['percentage'] = round(
                (result['duplicated_lines'] / result['total_lines']) * 100, 2
            )
        else:
            result['percentage'] = 0

        result['duplicates'] = result['clones']

        if result['percentage'] is not None:
            result['rating'] = calculate_rating('duplication', result['percentage'])
            result['badge_url'] = generate_badge_url(
                'duplication',
                f"{result['percentage']}%",
                result['rating']
            )
    except json.JSONDecodeError as e:
        print(f"Error parsing jscpd JSON: {e}")
    except Exception as e:
        print(f"Unexpected error parsing jscpd: {e}")

    return result


def load_historical_data(filepath: str, max_days: int = 90) -> list:
    """Load and prune historical data."""
    history = []

    if os.path.exists(filepath):
        try:
            with open(filepath, 'r') as f:
                history = json.load(f)
        except (json.JSONDecodeError, IOError):
            history = []

    # Prune old entries (keep last max_days)
    cutoff = datetime.now() - timedelta(days=max_days)
    cutoff_str = cutoff.isoformat()

    history = [
        entry for entry in history
        if entry.get('timestamp', '') >= cutoff_str
    ]

    return history


def save_historical_data(filepath: str, history: list) -> None:
    """Save historical data to JSON file."""
    os.makedirs(os.path.dirname(filepath), exist_ok=True)
    with open(filepath, 'w') as f:
        json.dump(history, f, indent=2)


def generate_badges(output_dir: str, metrics: dict) -> None:
    """Generate SVG badge files locally."""
    badges_dir = os.path.join(output_dir, 'badges')
    os.makedirs(badges_dir, exist_ok=True)

    badge_configs = [
        ('coverage', metrics.get('coverage', {}).get('line_coverage'), '%', 'coverage'),
        ('phpstan', metrics.get('phpstan', {}).get('errors'), ' errors', 'phpstan'),
        ('phpcs', metrics.get('phpcs', {}).get('violations'), ' issues', 'phpcs'),
        ('security', metrics.get('security', {}).get('issues'), ' issues', 'security'),
        ('phpmd', metrics.get('phpmd', {}).get('violations'), ' issues', 'phpcs'),  # Use phpcs thresholds
        ('duplication', metrics.get('jscpd', {}).get('percentage'), '%', 'duplication'),
    ]

    for label, value, suffix, metric_key in badge_configs:
        if value is not None:
            rating = calculate_rating(metric_key, value)
            color = COLORS.get(rating, 'lightgrey')

            # Create simple SVG badge
            svg = create_svg_badge(label, f"{value}{suffix}", color)

            badge_path = os.path.join(badges_dir, f"{label}.svg")
            with open(badge_path, 'w') as f:
                f.write(svg)


def create_svg_badge(label: str, value: str, color: str) -> str:
    """Create a simple SVG badge."""
    color_map = {
        'brightgreen': '#4c1',
        'green': '#97ca00',
        'yellow': '#dfb317',
        'red': '#e05d44',
        'lightgrey': '#9f9f9f',
    }
    hex_color = color_map.get(color, '#9f9f9f')

    label_width = len(label) * 7 + 10
    value_width = len(value) * 7 + 10
    total_width = label_width + value_width

    return f'''<svg xmlns="http://www.w3.org/2000/svg" width="{total_width}" height="20">
  <linearGradient id="b" x2="0" y2="100%">
    <stop offset="0" stop-color="#bbb" stop-opacity=".1"/>
    <stop offset="1" stop-opacity=".1"/>
  </linearGradient>
  <mask id="a">
    <rect width="{total_width}" height="20" rx="3" fill="#fff"/>
  </mask>
  <g mask="url(#a)">
    <rect width="{label_width}" height="20" fill="#555"/>
    <rect x="{label_width}" width="{value_width}" height="20" fill="{hex_color}"/>
    <rect width="{total_width}" height="20" fill="url(#b)"/>
  </g>
  <g fill="#fff" text-anchor="middle" font-family="DejaVu Sans,Verdana,Geneva,sans-serif" font-size="11">
    <text x="{label_width/2}" y="15" fill="#010101" fill-opacity=".3">{label}</text>
    <text x="{label_width/2}" y="14">{label}</text>
    <text x="{label_width + value_width/2}" y="15" fill="#010101" fill-opacity=".3">{value}</text>
    <text x="{label_width + value_width/2}" y="14">{value}</text>
  </g>
</svg>'''


def format_language_breakdown(by_language: dict) -> str:
    """Format language breakdown as HTML."""
    if not by_language:
        return ''

    parts = []
    for lang, stats in sorted(by_language.items()):
        percentage = stats.get('percentage', 0)
        clones = stats.get('clones', 0)
        parts.append(f"<strong>{lang.upper()}</strong>: {percentage}% ({clones} clones)")

    return ' | '.join(parts)


def format_ruleset_breakdown(by_ruleset: dict) -> str:
    """Format PHPMD ruleset breakdown as HTML."""
    if not by_ruleset:
        return ''

    parts = []
    for ruleset, count in sorted(by_ruleset.items()):
        # Shorten ruleset names
        short_name = ruleset.replace(' Rules', '').replace('Code ', '')
        parts.append(f"<strong>{short_name}</strong>: {count}")

    return ' | '.join(parts)


def generate_dashboard_html(template_path: str, output_path: str, metrics: dict, history: list, commit_sha: str) -> None:
    """Generate the dashboard HTML from template."""
    # Read template
    with open(template_path, 'r') as f:
        template = f.read()

    # Prepare template variables
    coverage = metrics.get('coverage', {})
    phpstan = metrics.get('phpstan', {})
    phpcs = metrics.get('phpcs', {})
    security = metrics.get('security', {})
    phpmd = metrics.get('phpmd', {})
    jscpd = metrics.get('jscpd', {})

    # Format history for charts
    chart_data = {
        'labels': [entry.get('date', '')[:10] for entry in history[-30:]],
        'coverage': [entry.get('coverage', {}).get('line_coverage') for entry in history[-30:]],
        'phpstan': [entry.get('phpstan', {}).get('errors') for entry in history[-30:]],
        'phpcs': [entry.get('phpcs', {}).get('violations') for entry in history[-30:]],
        'security': [entry.get('security', {}).get('issues') for entry in history[-30:]],
        'phpmd': [entry.get('phpmd', {}).get('violations') for entry in history[-30:]],
        'duplication': [entry.get('jscpd', {}).get('percentage') for entry in history[-30:]],
    }

    # Replace template variables
    replacements = {
        '{{ coverage_value }}': str(coverage.get('line_coverage', 'N/A')),
        '{{ coverage_rating }}': coverage.get('rating', 'N/A'),
        '{{ coverage_lines }}': f"{coverage.get('lines_covered', 0)}/{coverage.get('lines_total', 0)}",
        '{{ phpstan_errors }}': str(phpstan.get('errors', 'N/A')),
        '{{ phpstan_rating }}': phpstan.get('rating', 'N/A'),
        '{{ phpstan_files }}': str(phpstan.get('files_with_errors', 0)),
        '{{ phpcs_violations }}': str(phpcs.get('violations', 'N/A')),
        '{{ phpcs_rating }}': phpcs.get('rating', 'N/A'),
        '{{ phpcs_errors }}': str(phpcs.get('errors', 0)),
        '{{ phpcs_warnings }}': str(phpcs.get('warnings', 0)),
        '{{ security_issues }}': str(security.get('issues', 'N/A')),
        '{{ security_rating }}': security.get('rating', 'N/A'),
        '{{ security_high }}': str(security.get('high', 0)),
        '{{ security_medium }}': str(security.get('medium', 0)),
        '{{ phpmd_violations }}': str(phpmd.get('violations', 'N/A')),
        '{{ phpmd_rating }}': phpmd.get('rating', 'N/A'),
        '{{ phpmd_files }}': str(phpmd.get('files_affected', 0)),
        '{{ phpmd_rulesets }}': format_ruleset_breakdown(phpmd.get('by_ruleset', {})),
        '{{ duplication_percentage }}': str(jscpd.get('percentage', 'N/A')),
        '{{ duplication_rating }}': jscpd.get('rating', 'N/A'),
        '{{ duplication_clones }}': str(jscpd.get('clones', 0)),
        '{{ duplication_lines }}': str(jscpd.get('duplicated_lines', 0)),
        '{{ duplication_by_language }}': format_language_breakdown(jscpd.get('by_language', {})),
        '{{ chart_data_json }}': json.dumps(chart_data),
        '{{ generated_at }}': datetime.now().strftime('%Y-%m-%d %H:%M:%S UTC'),
        '{{ commit_sha }}': commit_sha[:8] if commit_sha else 'unknown',
        '{{ commit_sha_full }}': commit_sha or 'unknown',
    }

    html = template
    for key, value in replacements.items():
        html = html.replace(key, str(value) if value is not None else 'N/A')

    # Write output
    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    with open(output_path, 'w') as f:
        f.write(html)


def main():
    parser = argparse.ArgumentParser(description='Process PHP metrics and generate dashboard')
    parser.add_argument('--coverage', help='Path to coverage.xml')
    parser.add_argument('--phpstan', help='Path to phpstan.json')
    parser.add_argument('--phpcs', help='Path to phpcs.json')
    parser.add_argument('--security', help='Path to security-phpcs.json')
    parser.add_argument('--phpmd', help='Path to phpmd.json')
    parser.add_argument('--jscpd', help='Path to jscpd.json')
    parser.add_argument('--template', help='Path to dashboard HTML template')
    parser.add_argument('--output-dir', default='site/metrics', help='Output directory')
    parser.add_argument('--commit-sha', default='', help='Git commit SHA')
    parser.add_argument('--mock-data', action='store_true', help='Use mock data for testing')

    args = parser.parse_args()

    # Parse all metrics
    if args.mock_data:
        metrics = {
            'coverage': {'line_coverage': 75.5, 'lines_covered': 1500, 'lines_total': 2000, 'rating': 'B'},
            'phpstan': {'errors': 5, 'files_with_errors': 3, 'rating': 'B'},
            'phpcs': {'violations': 15, 'errors': 5, 'warnings': 10, 'rating': 'B'},
            'security': {'issues': 1, 'high': 0, 'medium': 1, 'rating': 'B'},
            'phpmd': {'violations': 10, 'files_affected': 5, 'by_ruleset': {'Code Size Rules': 8, 'Unused Code Rules': 2}, 'rating': 'B'},
            'jscpd': {'percentage': 2.5, 'clones': 5, 'duplicated_lines': 100, 'rating': 'A'},
        }
    else:
        metrics = {
            'coverage': parse_coverage_xml(args.coverage) if args.coverage else {},
            'phpstan': parse_phpstan_json(args.phpstan) if args.phpstan else {},
            'phpcs': parse_phpcs_json(args.phpcs) if args.phpcs else {},
            'security': parse_security_json(args.security) if args.security else {},
            'phpmd': parse_phpmd_json(args.phpmd) if args.phpmd else {},
            'jscpd': parse_jscpd_json(args.jscpd) if args.jscpd else {},
        }

    # Create output directory
    os.makedirs(args.output_dir, exist_ok=True)

    # Load and update historical data
    history_path = os.path.join(args.output_dir, 'history', 'all.json')
    history = load_historical_data(history_path)

    # Add current metrics to history
    current_entry = {
        'timestamp': datetime.now().isoformat(),
        'date': datetime.now().strftime('%Y-%m-%d'),
        'commit': args.commit_sha[:8] if args.commit_sha else 'unknown',
        'coverage': metrics.get('coverage', {}),
        'phpstan': metrics.get('phpstan', {}),
        'phpcs': metrics.get('phpcs', {}),
        'security': metrics.get('security', {}),
        'phpmd': metrics.get('phpmd', {}),
        'jscpd': metrics.get('jscpd', {}),
    }
    history.append(current_entry)
    save_historical_data(history_path, history)

    # Save current metrics as API endpoint
    api_path = os.path.join(args.output_dir, 'api', 'metrics.json')
    os.makedirs(os.path.dirname(api_path), exist_ok=True)
    with open(api_path, 'w') as f:
        json.dump({
            'generated_at': datetime.now().isoformat(),
            'commit': args.commit_sha,
            'metrics': metrics,
        }, f, indent=2)

    # Generate badges
    generate_badges(args.output_dir, metrics)

    # Generate dashboard HTML if template provided
    if args.template and os.path.exists(args.template):
        output_html = os.path.join(args.output_dir, 'index.html')
        generate_dashboard_html(args.template, output_html, metrics, history, args.commit_sha)
        print(f"Dashboard generated: {output_html}")
    else:
        print("Warning: No template provided, skipping HTML generation")

    print(f"Metrics processed successfully!")
    print(f"  - Coverage: {metrics.get('coverage', {}).get('line_coverage', 'N/A')}%")
    print(f"  - PHPStan errors: {metrics.get('phpstan', {}).get('errors', 'N/A')}")
    print(f"  - PHPCS violations: {metrics.get('phpcs', {}).get('violations', 'N/A')}")
    print(f"  - Security issues: {metrics.get('security', {}).get('issues', 'N/A')}")
    print(f"  - PHPMD violations: {metrics.get('phpmd', {}).get('violations', 'N/A')}")
    print(f"  - Duplication: {metrics.get('jscpd', {}).get('percentage', 'N/A')}%")


if __name__ == '__main__':
    main()
