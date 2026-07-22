#!/usr/bin/env python3
import os
import sys

# Set up Laravel environment
os.chdir('/home/juju5302/Bureau/mon stage/stage/gestion-projet')
os.environ.setdefault('APP_ENV', 'testing')
os.environ.setdefault('APP_DEBUG', 'true')

# Set up the environment
sys.path.insert(0, '/home/juju5302/Bureau/mon stage/stage/gestion-projet')

# Test the ProjetService fix
with open('test_projet_fix.py', 'w') as f:
    f.write('''
from tests/Feature/Daf/ProjetTest.php

# This is a simplified version of the failing test
# We want to test if the ProjetService.mettreEnCours method
# properly validates the project status

# Test case: When a project is already in EnCours status
# and we try to mise en cours it again, it should throw
# a ValidationException

# The ProjetService.mettreEnCours method should:
# 1. Check if the project is in EnAttenteFinancement status
# 2. If not, throw a ValidationException
# 3. If yes, proceed with the transaction

# We need to verify that this validation is working correctly
# by testing the ProjetService directly
''')

with open('test_projet_fix.py', 'r') as f:
    print(f.read())
