```bash
uv python install 3.14

mkdir my-project
cd my-project

uv init --python 3.14
uv python pin 3.14
uv venv

source .venv/bin/ativate

python --version

deativate

uv add fastapi
uv add "uvicorn[standard]"

uv run uvicorn main:app --reload
```
```bash
# ============================================================
# UV + PYTHON COMPLETE CHEAT SHEET
# Ubuntu / Linux
# ============================================================


# ============================================================
# 1. INSTALL UV
# ============================================================

# shortcut: Install uv
curl -LsSf https://astral.sh/uv/install.sh | sh

# shortcut: Reload Bash configuration
source ~/.bashrc

# shortcut: Check uv version
uv --version

# shortcut: If uv is not found, add uv's local bin directory to PATH
export PATH="$HOME/.local/bin:$PATH"

# shortcut: Check uv location
which uv


# ============================================================
# 2. PYTHON VERSION MANAGEMENT WITH UV
# ============================================================

# shortcut: List all Python versions available/installed through uv
uv python list

# shortcut: Install Python 3.10
uv python install 3.10

# shortcut: Install Python 3.11
uv python install 3.11

# shortcut: Install Python 3.12
uv python install 3.12

# shortcut: Install Python 3.13
uv python install 3.13

# shortcut: Install Python 3.14
uv python install 3.14

# shortcut: Install multiple Python versions at once
uv python install 3.10 3.11 3.12 3.13 3.14

# shortcut: Find the installed Python 3.14 executable
uv python find 3.14

# shortcut: Find the installed Python 3.13 executable
uv python find 3.13

# shortcut: Check installed Python versions
uv python list


# ============================================================
# 3. CREATE A NEW PROJECT
# ============================================================

# shortcut: Create a new project directory
mkdir project-b

# shortcut: Enter the project directory
cd project-b

# shortcut: Initialize a new uv Python project
uv init

# shortcut: Initialize a new project with a specific Python version
uv init --python 3.14


# ============================================================
# 4. SELECT / PIN PYTHON VERSION FOR A PROJECT
# ============================================================

# shortcut: Pin Python 3.14 for this project
uv python pin 3.14

# shortcut: Pin Python 3.13 for this project
uv python pin 3.13

# shortcut: Pin Python 3.12 for this project
uv python pin 3.12

# shortcut: Check which Python version is pinned
cat .python-version

# shortcut: Show the Python version selected by uv
uv run python --version


# ============================================================
# 5. CREATE VIRTUAL ENVIRONMENT
# ============================================================

# shortcut: Create a virtual environment using the pinned Python version
uv venv

# shortcut: Create a virtual environment using Python 3.13
uv venv --python 3.13

# shortcut: Create a virtual environment using Python 3.14
uv venv --python 3.14

# shortcut: Create a virtual environment using Python 3.12
uv venv --python 3.12


# ============================================================
# 6. ACTIVATE / DEACTIVATE VIRTUAL ENVIRONMENT
# ============================================================

# shortcut: Activate the project's virtual environment
source .venv/bin/activate

# shortcut: Check active Python version
python --version

# shortcut: Check which Python executable is currently active
which python

# shortcut: Check which pip executable is currently active
which pip

# shortcut: Exit/deactivate the virtual environment
deactivate


# ============================================================
# 7. RUN PYTHON WITHOUT ACTIVATING VENV
# ============================================================

# shortcut: Run Python using the project's uv environment
uv run python

# shortcut: Check project Python version without activating .venv
uv run python --version

# shortcut: Run a Python script using the project environment
uv run python main.py

# shortcut: Run any command inside the project's environment
uv run <command>

# shortcut: Run FastAPI with uvicorn
uv run uvicorn main:app --reload


# ============================================================
# 8. INSTALL PACKAGES WITH UV PIP
# ============================================================

# shortcut: Install one package
uv pip install requests

# shortcut: Install multiple packages
uv pip install requests flask django

# shortcut: Install a specific package version
uv pip install requests==2.32.3

# shortcut: Install a package with a minimum version
uv pip install "requests>=2.32"

# shortcut: Upgrade a package
uv pip install --upgrade requests

# shortcut: Upgrade multiple packages
uv pip install --upgrade requests flask

# shortcut: Uninstall a package
uv pip uninstall requests

# shortcut: Show installed packages
uv pip list

# shortcut: Show information about a specific package
uv pip show requests

# shortcut: Check outdated packages
uv pip list --outdated


# ============================================================
# 9. NORMAL PIP WITH THE PROJECT VENV
# ============================================================

# shortcut: Install package using Python's pip
python -m pip install requests

# shortcut: Install multiple packages using pip
python -m pip install requests flask

# shortcut: Upgrade a package using pip
python -m pip install --upgrade requests

# shortcut: Uninstall a package using pip
python -m pip uninstall requests

# shortcut: Show installed packages using pip
python -m pip list

# shortcut: Show package information
python -m pip show requests


# ============================================================
# 10. RECOMMENDED UV PROJECT DEPENDENCY MANAGEMENT
# ============================================================

# shortcut: Add a project dependency
uv add requests

# shortcut: Add multiple project dependencies
uv add requests fastapi

# shortcut: Add a specific package version
uv add "requests==2.32.3"

# shortcut: Add FastAPI
uv add fastapi

# shortcut: Add Uvicorn
uv add "uvicorn[standard]"

# shortcut: Add SQLAlchemy
uv add sqlalchemy

# shortcut: Add PostgreSQL driver
uv add psycopg

# shortcut: Add Redis
uv add redis

# shortcut: Remove a project dependency
uv remove requests

# shortcut: Sync project dependencies and virtual environment
uv sync


# ============================================================
# 11. UV LOCK FILE
# ============================================================

# shortcut: Create/update uv.lock and synchronize dependencies
uv lock

# shortcut: Upgrade dependencies according to project requirements
uv lock --upgrade

# shortcut: Sync the environment from pyproject.toml and uv.lock
uv sync


# ============================================================
# 12. REQUIREMENTS.TXT
# ============================================================

# shortcut: Install packages from requirements.txt using uv
uv pip install -r requirements.txt

# shortcut: Export installed packages to requirements.txt
uv pip freeze > requirements.txt

# shortcut: View requirements.txt
cat requirements.txt

# shortcut: Install requirements using normal pip
python -m pip install -r requirements.txt


# ============================================================
# 13. IMPORT EXISTING REQUIREMENTS.TXT INTO UV PROJECT
# ============================================================

# shortcut: Add dependencies from requirements.txt to the uv project
uv add -r requirements.txt

# shortcut: Synchronize the environment
uv sync


# ============================================================
# 14. CHANGE PYTHON VERSION OF AN EXISTING PROJECT
# ============================================================

# shortcut: Change this project's Python version to 3.13
uv python pin 3.13

# shortcut: Remove the old virtual environment
rm -rf .venv

# shortcut: Recreate the virtual environment using the new pinned version
uv venv

# shortcut: Activate the new virtual environment
source .venv/bin/activate

# shortcut: Verify the new Python version
python --version

# shortcut: Synchronize project dependencies
uv sync


# ============================================================
# 15. EXAMPLE: PYTHON 3.13 PROJECT
# ============================================================

# shortcut: Create project directory
mkdir project-c

# shortcut: Enter project directory
cd project-c

# shortcut: Initialize uv project
uv init

# shortcut: Install Python 3.13 if needed
uv python install 3.13

# shortcut: Pin Python 3.13 for this project
uv python pin 3.13

# shortcut: Create Python 3.13 virtual environment
uv venv

# shortcut: Activate virtual environment
source .venv/bin/activate

# shortcut: Check Python version
python --version

# shortcut: Add FastAPI
uv add fastapi

# shortcut: Add Uvicorn
uv add "uvicorn[standard]"

# shortcut: Add Requests
uv add requests

# shortcut: Synchronize environment
uv sync

# shortcut: Run application
uv run python main.py

# shortcut: Deactivate virtual environment
deactivate


# ============================================================
# 16. EXAMPLE: PYTHON 3.14 PROJECT
# ============================================================

# shortcut: Create project directory
mkdir project-d

# shortcut: Enter project directory
cd project-d

# shortcut: Initialize project with Python 3.14
uv init --python 3.14

# shortcut: Pin Python 3.14
uv python pin 3.14

# shortcut: Create Python 3.14 virtual environment
uv venv

# shortcut: Activate virtual environment
source .venv/bin/activate

# shortcut: Check Python version
python --version

# shortcut: Install FastAPI using uv
uv add fastapi

# shortcut: Install Uvicorn using uv
uv add "uvicorn[standard]"

# shortcut: Run application
uv run uvicorn main:app --reload

# shortcut: Deactivate virtual environment
deactivate


# ============================================================
# 17. RUN WITHOUT ACTIVATING VENV
# ============================================================

# shortcut: Check Python version
uv run python --version

# shortcut: Run Python
uv run python

# shortcut: Run Python script
uv run python main.py

# shortcut: Run FastAPI
uv run uvicorn main:app --reload

# shortcut: Run Django
uv run python manage.py runserver


# ============================================================
# 18. CHECK PROJECT INFORMATION
# ============================================================

# shortcut: Check project Python version
cat .python-version

# shortcut: Check Python version
uv run python --version

# shortcut: Check Python executable
uv run which python

# shortcut: Check installed packages
uv pip list

# shortcut: Check project configuration
cat pyproject.toml

# shortcut: Check dependency lock file
ls -la uv.lock


# ============================================================
# 19. GIT / PROJECT FILES
# ============================================================

# shortcut: Check project files
ls -la

# shortcut: Check Git status
git status

# shortcut: Add project files
git add .

# shortcut: Commit project changes
git commit -m "Initialize Python project with uv"

# shortcut: Do NOT commit virtual environment
# .venv should normally be added to .gitignore

# shortcut: Add uv virtual environment to .gitignore
echo ".venv/" >> .gitignore


# ============================================================
# 20. CLONE AN EXISTING UV PROJECT
# ============================================================

# shortcut: Clone project
git clone <repository-url>

# shortcut: Enter project
cd <project-directory>

# shortcut: Install/sync the project's Python environment and dependencies
uv sync

# shortcut: Activate virtual environment if desired
source .venv/bin/activate

# shortcut: Check Python version
python --version

# shortcut: Run project
uv run python main.py


# ============================================================
# 21. SYSTEM PYTHON vs UV PYTHON
# ============================================================

# shortcut: Check Ubuntu system Python
which python3

# shortcut: Check Ubuntu system Python version
python3 --version

# shortcut: Check uv-managed Python versions
uv python list

# shortcut: Find a specific uv Python version
uv python find 3.14

# IMPORTANT:
# Do NOT replace /usr/bin/python3 with a manual symbolic link.
# Keep Ubuntu's system Python untouched.
#
# uv manages project-specific Python versions independently.


# ============================================================
# 22. QUICK DAILY WORKFLOW
# ============================================================

# shortcut: Enter existing project
cd project-b

# shortcut: Check pinned Python version
cat .python-version

# shortcut: Create venv if it does not exist
uv venv

# shortcut: Activate venv
source .venv/bin/activate

# shortcut: Check Python
python --version

# shortcut: Add package
uv add requests

# shortcut: Sync dependencies
uv sync

# shortcut: Run application
uv run python main.py

# shortcut: Deactivate venv
deactivate


# ============================================================
# 23. QUICK PYTHON VERSION SWITCHING
# ============================================================

# shortcut: Use Python 3.12 for this project
uv python pin 3.12

# shortcut: Recreate venv
rm -rf .venv
uv venv

# shortcut: Use Python 3.13 for this project
uv python pin 3.13

# shortcut: Recreate venv
rm -rf .venv
uv venv

# shortcut: Use Python 3.14 for this project
uv python pin 3.14

# shortcut: Recreate venv
rm -rf .venv
uv venv


# ============================================================
# 24. MOST IMPORTANT COMMANDS
# ============================================================

# shortcut: Install Python version
uv python install 3.14

# shortcut: List Python versions
uv python list

# shortcut: Select Python version for current project
uv python pin 3.14

# shortcut: Create virtual environment
uv venv

# shortcut: Activate virtual environment
source .venv/bin/activate

# shortcut: Check Python
python --version

# shortcut: Add dependency
uv add <package>

# shortcut: Remove dependency
uv remove <package>

# shortcut: Install from requirements.txt
uv pip install -r requirements.txt

# shortcut: Synchronize project
uv sync

# shortcut: Run project
uv run python main.py

# shortcut: Deactivate virtual environment
deactivate
```

### ⭐ সবচেয়ে গুরুত্বপূর্ণ ধারণাটা

তোমার পুরো workflow-টা এভাবে মনে রাখো:

```text
                    UV
                     │
        ┌────────────┴────────────┐
        │                         │
   Python Manager          Project Manager
        │                         │
        ▼                         ▼
uv python install          uv init
3.12 / 3.13 / 3.14              │
        │                        ▼
        │                  uv python pin
        │                        │
        │                        ▼
        │                    uv venv
        │                        │
        │                        ▼
        │                 .venv/bin/python
        │                        │
        └────────────────────────┤
                                 ▼
                       uv add / uv pip
                                 │
                                 ▼
                         uv sync / uv run
```

**সবচেয়ে recommended modern pattern:**

```bash
uv python install 3.14

mkdir my-project
cd my-project

uv init --python 3.14
uv python pin 3.14
uv venv

uv add fastapi
uv add "uvicorn[standard]"

uv run uvicorn main:app --reload
```

এখানে `.venv` manually activate করাও বাধ্যতামূলক নয়—`uv run` নিজেই project environment ব্যবহার করে। আর যদি interactive shell-এ `python`, `pip` ইত্যাদি সরাসরি চালাতে চাও, তখন:

```bash
source .venv/bin/activate
```

এটাই তোমার **clean Ubuntu + multiple Python versions + project-wise isolation + uv workflow**।
