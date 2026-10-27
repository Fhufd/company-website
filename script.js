// Registration validation

function validateForm() {

    let name = document.getElementById("name").value.trim();
    let password = document.getElementById("password").value;

    if (name.length < 3) {

        alert("⚠️ Name must contain at least 3 characters.");

        return false;
    }

    if (password.length < 6) {

        alert("🔐 Password must contain at least 6 characters.");

        return false;
    }

    alert("✅ Registration details are valid!");

    return true;
}


// Password show / hide

function togglePassword() {

    let password = document.getElementById("password");

    if (password.type === "password") {

        password.type = "text";

    } else {

        password.type = "password";

    }
}


// Page loading effect

window.addEventListener("load", function () {

    document.body.classList.add("loaded");

});


// Login button loading

document.addEventListener("DOMContentLoaded", function () {

    let forms = document.querySelectorAll("form");

    forms.forEach(function(form) {

        form.addEventListener("submit", function() {

            let button = form.querySelector("button");

            if (button) {

                button.innerHTML = "⏳ Processing...";

                button.disabled = true;

            }

        });

    });

});