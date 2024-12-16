from fastapi import FastAPI, UploadFile, File, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, HttpUrl
from typing import Optional
from markitdown import MarkItDown
from bs4 import BeautifulSoup
import tempfile
import os
import logging
import requests

# Set up logging
logging.basicConfig(level=logging.DEBUG)
logger = logging.getLogger(__name__)

app = FastAPI(title="MarkItDown API")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

class TextInput(BaseModel):
    content: str
    options: Optional[dict] = None

class UrlInput(BaseModel):
    url: HttpUrl
    options: Optional[dict] = None

@app.post("/convert/text")
async def convert_text(text_input: TextInput):
    """Convert text or HTML to markdown"""
    try:
        logger.debug(f"Received content: {text_input.content}")
        
        # Create a temporary file with .html extension
        with tempfile.NamedTemporaryFile(mode='w', suffix='.html', delete=False) as tmp:
            tmp.write(text_input.content)
            tmp_path = tmp.name
        
        try:
            logger.debug(f"Created temporary file: {tmp_path}")
            converter = MarkItDown()
            result = converter.convert(tmp_path)
            logger.debug(f"Conversion result: {result.text_content}")
            return {"markdown": result.text_content}
        finally:
            # Clean up the temporary file
            os.unlink(tmp_path)
            logger.debug("Temporary file cleaned up")
            
    except Exception as e:
        logger.exception("Error during conversion")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/convert/file")
async def convert_file(file: UploadFile = File(...)):
    """Convert an uploaded file to markdown"""
    try:
        # Check if the file extension is supported
        supported_extensions = ['.pdf', '.docx', '.pptx', '.xlsx', '.wav', '.mp3', '.jpg', '.jpeg', '.png']
        _, ext = os.path.splitext(file.filename)
        if ext.lower() not in supported_extensions:
            raise HTTPException(status_code=400, detail="Unsupported file type")

        # Save the uploaded file to a temporary location
        with tempfile.NamedTemporaryFile(delete=False) as tmp:
            tmp.write(await file.read())
            tmp_path = tmp.name

        try:
            logger.debug(f"Uploaded file saved to: {tmp_path}")
            converter = MarkItDown()
            result = converter.convert(tmp_path, file_extension=ext)
            logger.debug(f"Conversion result: {result.text_content}")
            return {"markdown": result.text_content}
        finally:
            # Clean up the temporary file
            os.unlink(tmp_path)
            logger.debug("Temporary file cleaned up")

    except Exception as e:
        logger.exception("Error during file conversion")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/convert/url")
async def convert_url(url_input: UrlInput):
    """Fetch a URL and convert its content to markdown"""
    try:
        logger.debug(f"Fetching URL: {url_input.url}")
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        }
        response = requests.get(str(url_input.url), headers=headers)
        response.raise_for_status()

        # Parse the HTML content with BeautifulSoup
        soup = BeautifulSoup(response.content, 'html.parser')
        
        # Remove unwanted elements for all URLs
        for element in soup.find_all(['script', 'style', 'iframe', 'noscript']):
            element.decompose()

        # Create a temporary file with appropriate extension
        ext = '.html'
        with tempfile.NamedTemporaryFile(delete=False, suffix=ext) as tmp:
            # For Wikipedia, do additional cleaning
            if 'wikipedia.org' in str(url_input.url):
                # Focus on main content
                main_content = soup.find(id='mw-content-text')
                if main_content:
                    # Remove specific Wikipedia elements
                    for element in main_content.find_all(['sup', 'span.reference', 'span.citation', '.mw-editsection']):
                        element.decompose()
                    # Write only the main content
                    tmp.write(str(main_content).encode('utf-8'))
                else:
                    tmp.write(str(soup).encode('utf-8'))
            else:
                # For non-Wikipedia URLs, do general cleanup
                # Remove navigation, header, footer, and sidebar elements
                for element in soup.find_all(['nav', 'header', 'footer', 'aside']):
                    element.decompose()
                
                # Find the main content area (common patterns)
                main_content = soup.find(['main', 'article', 'div.content', 'div.main-content'])
                if main_content:
                    tmp.write(str(main_content).encode('utf-8'))
                else:
                    # If no main content area found, use the cleaned body
                    body = soup.find('body')
                    if body:
                        tmp.write(str(body).encode('utf-8'))
                    else:
                        tmp.write(str(soup).encode('utf-8'))
            
            tmp_path = tmp.name

        try:
            logger.debug(f"URL content saved to: {tmp_path}")
            converter = MarkItDown()
            
            # For Wikipedia URLs, pass the URL to help the converter identify it
            if 'wikipedia.org' in str(url_input.url):
                result = converter.convert(tmp_path, file_extension=ext, url=str(url_input.url))
            else:
                result = converter.convert(tmp_path)
            
            # Additional cleanup of the markdown content
            markdown_content = result.text_content
            
            # Remove citation brackets and cleanup
            markdown_content = re.sub(r'\[\d+\]', '', markdown_content)
            markdown_content = re.sub(r'\[\]', '', markdown_content)
            # Remove multiple consecutive newlines
            markdown_content = re.sub(r'\n\s*\n\s*\n+', '\n\n', markdown_content)
            
            logger.debug(f"Conversion result: {markdown_content}")
            return {"markdown": markdown_content}
        finally:
            # Clean up the temporary file
            os.unlink(tmp_path)
            logger.debug("Temporary file cleaned up")

    except requests.RequestException as e:
        logger.exception("Error fetching URL")
        raise HTTPException(status_code=500, detail=f"Error fetching URL: {str(e)}")
    except Exception as e:
        logger.exception("Error during URL conversion")
        raise HTTPException(status_code=500, detail=str(e))