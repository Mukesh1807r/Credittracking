// Function to apply theme on page load
function applyTheme() {
    const theme = localStorage.getItem('theme');
    if (theme === 'dark') {
        document.body.classList.add('dark');
    }
}

// Function to toggle and save theme
function toggleDark() {
    document.body.classList.toggle("dark");
    
    // Save the preference to localStorage
    if (document.body.classList.contains("dark")) {
        localStorage.setItem('theme', 'dark');
    } else {
        localStorage.setItem('theme', 'light');
    }
}

// Run applyTheme as soon as the script loads
applyTheme();