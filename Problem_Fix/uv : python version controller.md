অবশ্যই। Ubuntu-তে **Python 3.14 + `uv` + virtual environment + pip/requirements.txt**—সবকিছু cleanভাবে manage করার জন্য নিচের workflow-টা follow করতে পারো।

## 1. `uv` install করো

সবচেয়ে সহজ:

```bash
curl -LsSf https://astral.sh/uv/install.sh | sh
```

তারপর terminal reload:

```bash
source ~/.bashrc
```

যদি `zsh` ব্যবহার করো:

```bash
source ~/.zshrc
```

Check:

```bash
uv --version
```

যদি `uv: command not found` আসে:

```bash
export PATH="$HOME/.local/bin:$PATH"
```

তারপর:

```bash
uv --version
```

---

# 2. Python 3.14 install করো `uv` দিয়ে

Ubuntu system Python পরিবর্তন করার দরকার নেই। `uv` দিয়ে আলাদা Python 3.14 রাখাই ভালো।

```bash
uv python install 3.14
```

Check:

```bash
uv python list
```

আর:

```bash
uv python find 3.14
```

এতে Python 3.14-এর location দেখতে পারবে।

---

# 3. New Python 3.14 project তৈরি

ধরো:

```bash
mkdir my-project
cd my-project
```

তারপর:

```bash
uv init --python 3.14
```

Check:

```bash
ls -la
```

সাধারণত তুমি দেখবে:

```text
pyproject.toml
.python-version
```

---

# 4. Virtual Environment তৈরি

```bash
uv venv --python 3.14
```

এতে:

```text
.venv/
```

তৈরি হবে।

Check:

```bash
ls -la
```

---

# 5. Virtual Environment ON করা

Ubuntu Bash:

```bash
source .venv/bin/activate
```

তারপর terminal prompt-এর সামনে এরকম দেখতে পারো:

```text
(.venv) ashraful@ubuntu:~/my-project$
```

Check:

```bash
python --version
```

Expected:

```text
Python 3.14.x
```

এবং:

```bash
which python
```

দেখাবে:

```text
/home/ashraful/my-project/.venv/bin/python
```

---

# 6. Virtual Environment OFF করা

খুব simple:

```bash
deactivate
```

তাহলে:

```text
(.venv)
```

prompt থেকে চলে যাবে।

### আবার ON

```bash
source .venv/bin/activate
```

### গুরুত্বপূর্ণ

Virtual environment **delete/off করার জন্য `rm -rf .venv` করার দরকার নেই**।

`deactivate` = environment থেকে বের হওয়া।

`rm -rf .venv` = environment পুরো delete করা।

---

# 7. `uv` দিয়ে package install

ধরো FastAPI লাগবে:

```bash
uv add fastapi
```

এটা শুধু package install করেই থামে না—project dependency `pyproject.toml`-এও manage করে।

আর:

```bash
uv add "uvicorn[standard]"
```

তারপর:

```bash
uv sync
```

---

# 8. Package uninstall

যেমন FastAPI remove করতে:

```bash
uv remove fastapi
```

তারপর:

```bash
uv sync
```

---

# 9. `pip install` করতে চাইলে?

Virtual environment activate করার পরে normal pip ব্যবহার করতে পারো:

```bash
source .venv/bin/activate

python -m pip install requests
```

Check:

```bash
python -m pip list
```

Uninstall:

```bash
python -m pip uninstall requests
```

আমি `pip` এর বদলে এই form recommend করি:

```bash
python -m pip install package_name
```

কারণ এতে কোন Python-এর pip ব্যবহার হচ্ছে সেটা পরিষ্কার থাকে।

---

# 10. কিন্তু `uv` ব্যবহার করলে pip লাগবে?

Modern project-এর জন্য **সাধারণত না**।

যেমন:

```bash
uv add requests
```

এর বদলে:

```bash
pip install requests
```

করার দরকার নেই।

`uv` dependency management করবে।

---

# 11. Existing `requirements.txt` থাকলে

ধরো project-এ আছে:

```text
requirements.txt
```

এর ভিতরে:

```text
fastapi
uvicorn
requests
pandas
```

### Option 1 — `uv` দিয়ে install

Virtual environment থাকলে:

```bash
uv pip install -r requirements.txt
```

এটা `requirements.txt` অনুযায়ী package install করবে।

---

# 12. `requirements.txt` থেকে proper `uv` project বানাতে চাইলে

আমি এটা বেশি recommend করব।

প্রথমে:

```bash
uv init --python 3.14
```

তারপর:

```bash
uv add -r requirements.txt
```

এতে dependencies `pyproject.toml`-এ চলে যাবে।

তারপর:

```bash
uv sync
```

এরপর future-এ `requirements.txt` maintain করার প্রয়োজন অনেক কমে যাবে।

---

# 13. `requirements.txt` generate করতে চাইলে

যদি তোমার project-এর dependencies থেকে `requirements.txt` বানাতে চাও:

```bash
uv pip freeze > requirements.txt
```

তারপর:

```bash
cat requirements.txt
```

দেখতে পারবে।

---

# 14. Existing `requirements.txt` + `.venv` workflow

ধরো GitLab থেকে project clone করেছ:

```bash
git clone <repository>
cd project
```

তারপর:

```bash
uv python install 3.14
uv venv --python 3.14
```

Activate:

```bash
source .venv/bin/activate
```

Install:

```bash
uv pip install -r requirements.txt
```

Run:

```bash
python main.py
```

---

# 15. Recommended modern `uv` workflow

নতুন project হলে আমি তোমাকে এই structure recommend করব:

```text
my-project/
├── .venv/
├── .python-version
├── pyproject.toml
├── uv.lock
├── src/
│   └── ...
└── README.md
```

Create:

```bash
mkdir my-project
cd my-project

uv init --python 3.14
uv venv
```

Package:

```bash
uv add fastapi
uv add "uvicorn[standard]"
uv add sqlalchemy
uv add psycopg
uv add redis
```

Run:

```bash
uv run python main.py
```

অথবা:

```bash
uv run uvicorn main:app --reload
```

এখানে `.venv` manually activate না করেও `uv run` environment ব্যবহার করতে পারে।

---

# 16. সবচেয়ে useful commands — Cheat Sheet

| কাজ                  | Command                                            |
| -------------------- | -------------------------------------------------- |
| uv install           | `curl -LsSf https://astral.sh/uv/install.sh \| sh` |
| uv version           | `uv --version`                                     |
| Python list          | `uv python list`                                   |
| Python 3.14 install  | `uv python install 3.14`                           |
| Project create       | `uv init --python 3.14`                            |
| venv create          | `uv venv --python 3.14`                            |
| venv ON              | `source .venv/bin/activate`                        |
| venv OFF             | `deactivate`                                       |
| Package add          | `uv add requests`                                  |
| Package remove       | `uv remove requests`                               |
| Sync                 | `uv sync`                                          |
| Run Python           | `uv run python main.py`                            |
| requirements install | `uv pip install -r requirements.txt`               |
| requirements export  | `uv pip freeze > requirements.txt`                 |
| pip install          | `python -m pip install requests`                   |
| pip uninstall        | `python -m pip uninstall requests`                 |
| Installed packages   | `uv pip list`                                      |

## ⭐ তোমার জন্য আমার recommended approach

তুমি যেহেতু **FastAPI / Django / AI/ML / backend projects** নিয়ে কাজ করছ, নতুন project-এ আমি এই pattern রাখতাম:

```bash
uv python install 3.14

mkdir my-project
cd my-project

uv init --python 3.14
uv venv

uv add fastapi
uv add "uvicorn[standard]"
uv add sqlalchemy
uv add psycopg
uv add redis

uv sync

uv run uvicorn main:app --reload
```

**একটা গুরুত্বপূর্ণ বিষয়:** Ubuntu-এর `/usr/bin/python3` বা system Python-কে Python 3.14 দিয়ে replace করার চেষ্টা করো না। `uv` দিয়ে project-specific Python 3.14 রাখলে system tools, `apt`, Ubuntu utilities ইত্যাদি ভাঙার risk থাকে না।
