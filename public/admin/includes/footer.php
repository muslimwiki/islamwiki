        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/admin/assets/js/admin.js"></script>
    <script>
        // Initialize any admin scripts here
        document.addEventListener('DOMContentLoaded', function() {
            // Example: Update server status on page load
            fetch('/admin/api/server-status')
                .then(response => response.json())
                .then(data => {
                    const statusEl = document.getElementById('server-status');
                    if (statusEl) {
                        statusEl.innerHTML = `
                            <p>Status: <span class="status-${data.status}">${data.status}</span></p>
                            <p>Uptime: ${data.uptime}</p>
                            <p>Memory: ${data.memory_usage}%</p>
                        `;
                    }
                });
        });
    </script>
</body>
</html>
