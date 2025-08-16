#!/usr/bin/env python3
"""Hadith Import Script for Islam Wiki"""

import os
import sys
import time
import json
import requests
import pymysql
from pymysql.cursors import DictCursor
from datetime import datetime

# Add project root to path
sys.path.append(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))))

# Import config
from scripts.hadith_import.config import (
    SUNNAH_API_BASE_URL, SUNNAH_API_KEY,
    DB_CONFIG, BATCH_SIZE, REQUEST_DELAY
)

class SunnahAPI:
    """Client for Sunnah.com API"""
    
    def __init__(self, api_key: str):
        self.base_url = SUNNAH_API_BASE_URL
        self.headers = {'x-api-key': api_key, 'Accept': 'application/json'}
    
    def get_collections(self) -> list:
        """Get list of hadith collections"""
        try:
            response = requests.get(f"{self.base_url}/collections", headers=self.headers)
            response.raise_for_status()
            return response.json().get('data', [])
        except Exception as e:
            print(f"Error fetching collections: {e}")
            return []

class HadithImporter:
    """Handles importing hadith data"""
    
    def __init__(self):
        if not SUNNAH_API_KEY:
            raise ValueError("SUNNAH_COM_API_KEY environment variable not set")
        self.api = SunnahAPI(SUNNAH_API_KEY)
        self.db = pymysql.connect(**DB_CONFIG, cursorclass=DictCursor)
        self.cursor = self.db.cursor()
    
    def __enter__(self):
        return self
    
    def __exit__(self, *args):
        self.db.close()
    
    def import_collections(self):
        """Import all collections"""
        print("Starting hadith import...")
        collections = self.api.get_collections()
        print(f"Found {len(collections)} collections")
        
        for col in collections:
            print(f"\nProcessing collection: {col.get('title')}")
            self._import_collection(col)
        
        print("\nHadith import completed!")
    
    def _import_collection(self, col_data: dict):
        """Import a single collection"""
        try:
            # Check if collection exists
            self.cursor.execute(
                "SELECT id FROM hadith_collections WHERE name = %s",
                (col_data['name'],)
            )
            if self.cursor.fetchone():
                print(f"  - Collection already exists: {col_data['name']}")
                return
            
            # Insert new collection
            self.cursor.execute(
                """
                INSERT INTO hadith_collections 
                (name, title, arabic_name, created_at, updated_at)
                VALUES (%s, %s, %s, %s, %s)
                """,
                (
                    col_data['name'],
                    col_data.get('title', ''),
                    col_data.get('arabicTitle', ''),
                    datetime.now(),
                    datetime.now()
                )
            )
            self.db.commit()
            print(f"  + Added collection: {col_data['name']}")
            
        except Exception as e:
            print(f"  ! Error importing collection: {e}")
            self.db.rollback()

if __name__ == "__main__":
    print("Hadith Importer for Islam Wiki")
    print("-----------------------------")
    
    try:
        with HadithImporter() as importer:
            importer.import_collections()
    except Exception as e:
        print(f"Error: {e}")
        sys.exit(1)
