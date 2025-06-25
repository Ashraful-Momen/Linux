run the command for cpp file : 
------------------------------
=> g++ -o left_rotation "1 - left_rotation.cpp" && ./left_rotation

====================== explain the command =========================
Let me break down the command step by step:

### **Command Breakdown:**
```bash
g++ -o left_rotation "1 - left_rotation.cpp" && ./left_rotation
```

#### **1. `g++`**
- This is the **GNU C++ compiler**, used to compile C++ source code into an executable program.

#### **2. `-o left_rotation`**
- `-o` stands for **output**.
- `left_rotation` is the name of the **executable file** that will be generated after compilation.
- If you omit `-o`, the default output name is usually `a.out` (on Linux/macOS).

#### **3. `"1 - left_rotation.cpp"`**
- This is the **input C++ source file**.
- The **quotes (`"`)** are necessary because the filename contains **spaces**.
- Without quotes, the shell would treat `1`, `-`, and `left_rotation.cpp` as separate arguments, causing an error.

#### **4. `&&`**
- This is a **logical AND** operator in the shell.
- It means:  
  - **First, run the command before `&&` (`g++ ...`).**  
  - **Only if it succeeds (compiles without errors), then run the next command (`./left_rotation`).**

#### **5. `./left_rotation`**
- This **runs the compiled executable**.
- `./` means "look in the current directory" (Linux/macOS requires this to run local executables).

---

### **What the Entire Command Does:**
1. **Compiles** `1 - left_rotation.cpp` into an executable named `left_rotation`.
2. **If compilation succeeds**, it immediately **runs** the program (`left_rotation`).

---

### **Alternative (Without `&&`)**
If you run them separately:
```bash
g++ -o left_rotation "1 - left_rotation.cpp"  # Compile first
./left_rotation                               # Run only if compilation worked
```

### **Common Errors & Fixes:**
1. **`g++: command not found`**  
   → Install `g++` with:  
     ```bash
     sudo apt install g++   # On Debian/Ubuntu/Kali
     ```

2. **`No such file or directory`**  
   → Make sure the filename is correct (check spelling and spaces).

3. **Permission Denied**  
   → Make the file executable:  
     ```bash
     chmod +x left_rotation
     ```

Would you like a deeper explanation of any part?
