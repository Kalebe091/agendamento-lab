    </div>

    <!-- Scripts Base -->
    <script>
        window.USER_ROLE = '<?php echo isset($current_user['role']) ? $current_user['role'] : ''; ?>';
    </script>
    <script src="../assets/js/app_global.js?v=3"></script>
    <script src="../assets/js/app_admin.js?v=1"></script>
    <?php if (isset($extra_js)) echo $extra_js; ?>
</body>
</html>
