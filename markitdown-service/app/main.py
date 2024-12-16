from fastapi import FastAPI, UploadFile, File, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, HttpUrl
from typing import Optional
from markitdown import MarkItDown
import tempfile
import os
import logging

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