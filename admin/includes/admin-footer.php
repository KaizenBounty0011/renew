            </div><!-- .admin-content -->
        </div><!-- .admin-main -->
    </div><!-- .admin-layout -->

    <script>
        // Sidebar toggle
        const sidebar = document.getElementById('adminSidebar');
        const toggle = document.getElementById('sidebarToggle');
        const close = document.getElementById('sidebarClose');

        if (toggle) toggle.addEventListener('click', () => sidebar.classList.toggle('active'));
        if (close) close.addEventListener('click', () => sidebar.classList.remove('active'));

        // Delete confirmation
        document.querySelectorAll('[data-confirm]').forEach(el => {
            el.addEventListener('click', function(e) {
                if (!confirm(this.dataset.confirm)) e.preventDefault();
            });
        });
    </script>
</body>
</html>
