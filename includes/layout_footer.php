        </div> <!-- End Content Wrapper -->
    </div> <!-- End Main Content -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Sidebar Toggle Logic
        document.getElementById('sidebarToggle').addEventListener('click', function(){
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('expanded');
        });

        // Mobile responsiveness
        if(window.innerWidth < 768) {
            document.getElementById('sidebar').classList.add('collapsed');
        }
    </script>
</body>
</html>