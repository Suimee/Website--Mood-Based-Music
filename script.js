// Toggle between Login and Sign Up forms
document.getElementById("showSignUp").addEventListener("click", function () {
    document.getElementById("login").style.display = "none";
    document.getElementById("signup").style.display = "block";
});

document.getElementById("showLogin").addEventListener("click", function () {
    document.getElementById("signup").style.display = "none";
    document.getElementById("login").style.display = "block";
});