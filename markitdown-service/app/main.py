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
        converter = MarkItDown()
        result = converter.convert(text_input.content)
        logger.debug(f"Conversion result: {result.text_content}")
        return {"markdown": result.text_content}
    except Exception as e:
        logger.exception("Error during conversion")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/convert/file")
async def convert_file(file: UploadFile = File(...)):
    """Convert an uploaded file to markdown"""
    try:
        # Save the uploaded file to a temporary location
        with tempfile.NamedTemporaryFile(delete=False) as tmp:
            tmp.write(await file.read())
            tmp_path = tmp.name

        try:
            logger.debug(f"Uploaded file saved to: {tmp_path}")
            converter = MarkItDown()
            # Pass the original file extension to help with type detection
            _, ext = os.path.splitext(file.filename)
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
        converter = MarkItDown()
        result = converter.convert(str(url_input.url))
        logger.debug(f"Conversion result: {result.text_content}")
        return {"markdown": result.text_content}
    except Exception as e:
        logger.exception("Error during URL conversion")
        raise HTTPException(status_code=500, detail=str(e))