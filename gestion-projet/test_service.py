#!/usr/bin/env python3
import os
import sys

# Set up Laravel environment
os.chdir('/home/juju5302/Bureau/mon stage/stage/gestion-projet')
os.environ.setdefault('APP_ENV', 'testing')
os.environ.setdefault('APP_DEBUG', 'true')

# Load Laravel
from django.core.management import execute_from_command_line
from django.apps import apps

# Mock the necessary components
import sys

# Add a simple print to see what's happening
sys.path.insert(0, '/home/juju5302/Bureau/mon stage/stage/gestion-projet')

# Try to directly test the ProjetService logic
print("Testing ProjetService logic directly...")

# Load the service class
exec(open('app/Services/ProjetService.php').read())
