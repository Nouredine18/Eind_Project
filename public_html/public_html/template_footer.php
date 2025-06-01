        </div> <!-- End #dynamic-page-content-wrapper -->
      </div> <!-- End .main-panel -->
    </div> <!-- End .wrapper -->

    <!-- Core JS Files -->
    <script src="assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>

    <!-- jQuery Scrollbar -->
    <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

    <!-- Chart JS -->
    <script src="assets/js/plugin/chart.js/chart.min.js"></script>

    <!-- jQuery Sparkline -->
    <script src="assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>

    <!-- Chart Circle -->
    <script src="assets/js/plugin/chart-circle/circles.min.js"></script>

    <!-- Datatables -->
    <script src="assets/js/plugin/datatables/datatables.min.js"></script>

    <!-- Bootstrap Notify -->
    <script src="assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

    <!-- jQuery Vector Maps -->
    <script src="assets/js/plugin/jsvectormap/jsvectormap.min.js"></script>
    <script src="assets/js/plugin/jsvectormap/world.js"></script>

    <!-- Sweet Alert -->
    <script src="assets/js/plugin/sweetalert/sweetalert.min.js"></script>

    <!-- Kaiadmin JS -->
    <script src="assets/js/kaiadmin.min.js"></script>
    
    <?php /* Demo scripts are already commented out from previous steps */ ?>
    <!-- <script src="assets/js/setting-demo.js"></script> -->
    <!-- <script src="assets/js/demo.js"></script> -->

    <script>
      // Initial call for sparklines on first page load
      function initializeSparklines() {
          if ($("#lineChart").length) {
            $("#lineChart").sparkline([102, 109, 120, 99, 110, 105, 115], {
              type: "line", height: "70", width: "100%", lineWidth: "2",
              lineColor: "#177dff", fillColor: "rgba(23, 125, 255, 0.14)",
            });
          }
          if ($("#lineChart2").length) {
            $("#lineChart2").sparkline([99, 125, 122, 105, 110, 124, 115], {
              type: "line", height: "70", width: "100%", lineWidth: "2",
              lineColor: "#f3545d", fillColor: "rgba(243, 84, 93, .14)",
            });
          }
          if ($("#lineChart3").length) {
            $("#lineChart3").sparkline([105, 103, 123, 100, 95, 105, 115], {
              type: "line", height: "70", width: "100%", lineWidth: "2",
              lineColor: "#ffa534", fillColor: "rgba(255, 165, 52, .14)",
            });
          }
      }
      initializeSparklines(); // Call on initial load

      // AJAX Navigation Logic
      $(document).ready(function() {
        const contentWrapper = $('#dynamic-page-content-wrapper');
        
        function loadPage(url, pushState = true) {
            // Add a loading indicator if desired
            // contentWrapper.html('<p class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i> Loading...</p>');

            $.ajax({
                url: url + (url.includes('?') ? '&' : '?') + 'ajax_load_content=1',
                type: 'GET',
                dataType: 'json', // Expect JSON response
                success: function(response) {
                    if (response.html !== undefined && response.title !== undefined) {
                        contentWrapper.html(response.html);
                        document.title = response.title + " - Ecoligo";

                        if (pushState) {
                            history.pushState({ path: url, title: response.title }, response.title, url);
                        }
                        
                        // Re-initialize plugins or scripts for the new content
                        // This is crucial and might need to be specific per page or plugin
                        initializeSparklines(); // Example for sparklines
                        if (typeof initializeKaiadminPlugins === 'function') {
                           // initializeKaiadminPlugins(); // A hypothetical function to re-init theme JS
                        }
                        // Manually trigger DOMContentLoaded for scripts that listen to it, if any in loaded content
                        // $(contentWrapper).find('script').each(function() { eval($(this).text()); }); // Be careful with eval

                        // Update active sidebar link
                        updateSidebarActiveState(url);

                    } else {
                        // Fallback or error message
                        contentWrapper.html('<p class="alert alert-danger">Error: Invalid content received.</p>');
                        if (!pushState) { // If it was a popstate, try full reload
                           // window.location.href = url;
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error, xhr.responseText);
                    // Fallback to normal navigation on error
                    // window.location.href = url;
                     contentWrapper.html('<p class="alert alert-danger">Failed to load page content. Error: '+ status + '</p>');
                }
            });
        }

        function updateSidebarActiveState(url) {
            const pageName = url.substring(url.lastIndexOf('/') + 1).split('?')[0];
            $('.sidebar .nav-item').removeClass('active');
            $('.sidebar .nav-collapse .nav-item').removeClass('active'); // For sub-items

            $('.sidebar .nav-link.ajax-link').each(function() {
                const linkHref = $(this).attr('href');
                if (linkHref === pageName || (pageName === 'index.php' && linkHref === 'home.php')) { // Adjust for index/home
                    const parentLi = $(this).closest('li');
                    parentLi.addClass('active');
                    
                    // If it's a sub-item, also activate its parent collapse menu
                    const parentCollapse = parentLi.closest('.collapse');
                    if (parentCollapse.length) {
                        parentCollapse.addClass('show');
                        parentCollapse.prev('a').attr('aria-expanded', 'true').removeClass('collapsed');
                        parentCollapse.closest('.nav-item').addClass('active'); // Activate the main menu item
                    }
                    return false; // Exit each loop
                }
            });
        }


        // Intercept clicks on elements with class 'ajax-link'
        $(document).on('click', 'a.ajax-link', function(e) {
            e.preventDefault();
            const href = $(this).attr('href');
            // Avoid loading external links or javascript:void(0) via AJAX
            if (href && href !== '#' && !href.startsWith('javascript:') && !href.startsWith('http://') && !href.startsWith('https://')) {
                loadPage(href);
            }
        });

        // Handle browser back/forward buttons
        $(window).on('popstate', function(e) {
            if (e.originalEvent.state && e.originalEvent.state.path) {
                loadPage(e.originalEvent.state.path, false);
            } else {
                // If no state, might be initial page or external, let browser handle or reload current
                // For simplicity, could reload the current path if it's an internal page
                if (location.pathname.endsWith('.php')) { // Basic check for internal page
                   // loadPage(location.pathname + location.search, false);
                }
            }
        });

        // Store initial state for back button to first page
        if (history.state === null) {
            history.replaceState({ path: window.location.href, title: document.title }, document.title, window.location.href);
        }
      });
    </script>
  </body>
</html>
