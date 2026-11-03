        </main>
    </div>
    
    <script src="../assets/js/admin.js"></script>
    <script>
        // User dropdown toggle
        function toggleUserDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('user-dropdown');
            dropdown.classList.toggle('open');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const dropdown = document.getElementById('user-dropdown');
            if (dropdown && !dropdown.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });
        
        // Close dropdown on ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const dropdown = document.getElementById('user-dropdown');
                if (dropdown) dropdown.classList.remove('open');
            }
        });
        
        // Theme icon toggle based on current theme
        function updateThemeIcon() {
            const theme = document.documentElement.getAttribute('data-theme');
            const lightIcon = document.querySelector('.theme-icon-light');
            const darkIcon = document.querySelector('.theme-icon-dark');
            
            if (lightIcon && darkIcon) {
                if (theme === 'dark') {
                    lightIcon.style.display = 'none';
                    darkIcon.style.display = 'block';
                } else {
                    lightIcon.style.display = 'block';
                    darkIcon.style.display = 'none';
                }
            }
        }
        
        // Initialize theme icon
        updateThemeIcon();
        
        // Watch for theme changes
        const observer = new MutationObserver(updateThemeIcon);
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
        
        // Keyboard shortcut for search (Cmd/Ctrl + K)
        document.addEventListener('keydown', (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                const searchBtn = document.querySelector('.global-search');
                if (searchBtn) searchBtn.click();
            }
        });
    </script>
</body>
</html>