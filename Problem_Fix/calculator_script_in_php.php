<?php
// Function to add two numbers
function add($num1, $num2) {
    return $num1 + $num2;
}

// Function to subtract two numbers
function subtract($num1, $num2) {
    return $num1 - $num2;
}

// Function to multiply two numbers
function multiply($num1, $num2) {
    return $num1 * $num2;
}

// Function to divide two numbers
function divide($num1, $num2) {
    if ($num2 == 0) {
        return "Error: Cannot divide by zero";
    } else {
        return $num1 / $num2;
    }
}

// Main program
echo "Welcome to the PHP Calculator!\n";

// Loop until the user chooses to exit
while (true) {
    // Prompt the user to enter values for num1 and num2
    echo "Enter the first number: ";
    $num1 = (float) fgets(STDIN);
    echo "Enter the second number: ";
    $num2 = (float) fgets(STDIN);

    // Perform calculations and display results
    echo "Addition: " . add($num1, $num2) . "\n";
    echo "Subtraction: " . subtract($num1, $num2) . "\n";
    echo "Multiplication: " . multiply($num1, $num2) . "\n";
    echo "Division: " . divide($num1, $num2) . "\n";

    // Prompt the user to continue or exit
    echo "Do you want to continue (yes/no)? ";
    $choice = trim(fgets(STDIN));
    if (strtolower($choice) !== 'yes') {
        echo "Exiting the PHP Calculator. Goodbye!\n";
        break;
    }
}
?>
