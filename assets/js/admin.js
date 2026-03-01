document.addEventListener('DOMContentLoaded', () => {
    // 1️⃣ Load Dashboard by default
    const dashboardLink = document.querySelector('#nav-dashboard');
    if (dashboardLink) {
        const page = dashboardLink.getAttribute('data-page');
        fetch(page)
            .then(res => res.text())
            .then(data => {
                document.getElementById('main-content').innerHTML = data;
            })
            .catch(err => {
                document.getElementById('main-content').innerHTML = "<p class='text-danger'>Error loading Dashboard!</p>";
            });
    }
    document.querySelectorAll(`.sidebar a`).forEach(link => {
        link.addEventListener('click', function(e) {
            //e.preventDefault(); // stop normal link reload

            const page = this.getAttribute('data-page'); // get target page
            //load page content using fatch
            if (!page) return;
            fetch(page)
                .then(response => response.text()) // convert response to text
                .then(data => {
                    document.getElementById('main-content').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('main-content').innerHTML = "<p class='text-danger'>Error load page !</p> ";
                });
        });
    });
});