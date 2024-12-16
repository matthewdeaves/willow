from fastapi import FastAPI, UploadFile, File, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, HttpUrl
from typing import Optional
from markitdown import MarkItDown
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
        response = requests.get(str(url_input.url))
        response.raise_for_status()

        # Create a temporary file with appropriate extension
        ext = '.html'
        with tempfile.NamedTemporaryFile(delete=False, suffix=ext) as tmp:
            tmp.write(response.content)
            tmp_path = tmp.name

        try:
            logger.debug(f"URL content saved to: {tmp_path}")
            converter = MarkItDown()
            
            # If it's a Wikipedia URL, we can add specific options
            if 'wikipedia.org' in str(url_input.url):
                options = {
                    'remove_scripts': True,
                    'remove_styles': True,
                    'focus_content': True,
                    'clean_links': True
                }
                result = converter.convert(tmp_path, options=options)
            else:
                result = converter.convert(tmp_path)
                
            logger.debug(f"Conversion result: {result.text_content}")
            return {"markdown": result.text_content}
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