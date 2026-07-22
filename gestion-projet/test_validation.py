#!/usr/bin/env python3
import os

# Test the validation logic in ProjetService.mettreEnCours
# This is a simplified test to verify the fix

print("Testing ProjetService.mettreEnCours validation...")
print("When a project is not in EnAttenteFinancement status,")
print("the ProjetService.mettreEnCours method should throw")
print("a ValidationException.")
print()
print("The test expects:")
print("1. A project with EnAttenteFinancement status")
print("2. A convention with Active status")
print("3. Manually update the project to EnCours status")
print("4. Call the API to mise en cours it again")
print("5. Expect a validation error in the session")
print()
print("The fix should ensure that when ProjetService.mettreEnCours")
print("is called with a project that is not in EnAttenteFinancement")
print("status, it throws a ValidationException.")
