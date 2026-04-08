<!-- /.content-wrapper -->
<footer class="main-footer" style="background-color:#2c3e50 !important;">
    <strong>Copyright &copy; 2025 PLAY2REVIEW GAME.</strong>

    <div class="float-right d-none d-sm-inline-block">
        All rights reserved.
    </div>
</footer>

<!-- jQuery -->
<script src="assets/plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="assets/plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
    $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="assets/plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="assets/plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="assets/plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="assets/plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="assets/plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="assets/plugins/moment/moment.min.js"></script>
<script src="assets/plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="assets/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="assets/plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="assets/dist/js/adminlte.js"></script>

<!-- Select2 -->
<script src="assets/plugins/select2/js/select2.full.min.js"></script>
<!-- Bootstrap4 Duallistbox -->
<script src="assets/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script>
<!-- InputMask -->
<script src="assets/plugins/moment/moment.min.js"></script>
<script src="assets/plugins/inputmask/jquery.inputmask.min.js"></script>
<!-- date-range-picker -->
<script src="assets/plugins/daterangepicker/daterangepicker.js"></script>
<!-- bootstrap color picker -->
<script src="assets/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
<!-- Bootstrap Switch -->
<script src="assets/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<!-- BS-Stepper -->
<script src="assets/plugins/bs-stepper/js/bs-stepper.min.js"></script>
<!-- dropzonejs -->
<script src="assets/plugins/dropzone/min/dropzone.min.js"></script>

<!-- Page specific script -->
<script>
    // elements.js — safe to always load, jQuery selectors on missing elements are no-ops
    // but guard the datetimepicker calls that crash on null
</script>
<script src="JS/elements.js"></script>
<script>
    // charts.js — only load if #areaChart canvas exists on this page
    $(function() {
        if ($('#areaChart').length) {
            $.getScript('JS/charts.js');
        }
    });
</script>


<!-- bs-custom-file-input -->
<script src="assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>


<script>
    $(function () {
        bsCustomFileInput.init();
    });
</script>

<!-- DataTables  & Plugins -->
<script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="assets/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="assets/plugins/jszip/jszip.min.js"></script>
<script src="assets/plugins/pdfmake/pdfmake.min.js"></script>
<script src="assets/plugins/pdfmake/vfs_fonts.js"></script>
<script src="assets/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="assets/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="assets/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>

<script>
    $(function () {
        $("#example1").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "buttons": [{
                extend: 'copy',
                exportOptions: {
                    columns: "thead th:not(.noExport)"
                }
            }, {
                extend: "csv",
                exportOptions: {
                    columns: "thead th:not(.noExport)"
                }
            }, {
                extend: "excel",
                exportOptions: {
                    columns: "thead th:not(.noExport)"
                }
            }, {
                extend: "pdf",
                exportOptions: {
                    columns: "thead th:not(.noExport)"
                }
            }, {
                extend: "print",
                exportOptions: {
                    columns: "thead th:not(.noExport)"
                }
            }]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');

        $('#example2').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
        });

        $('#chatbot').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
        });

        $('#areaConfig').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
        });

        $('#report').DataTable({
            "paging": false,
            "lengthChange": true,
            "searching": false,
            "ordering": false,
            "info": false,
            "autoWidth": false,
            "responsive": true,
        });
    });
</script>

<!-- Summernote -->
<script src="assets/plugins/summernote/summernote-bs4.min.js"></script>
<!-- CodeMirror -->
<script src="assets/plugins/codemirror/codemirror.js"></script>
<script src="assets/plugins/codemirror/mode/css/css.js"></script>
<script src="assets/plugins/codemirror/mode/xml/xml.js"></script>
<script src="assets/plugins/codemirror/mode/htmlmixed/htmlmixed.js"></script>
<!-- Page specific script -->
<script>
    $(function () {
        // Summernote — only init if element exists
        var summernoteIds = ['#about','#remarksOpenCase','#remarksOnGoing','#remarksDismissed',
            '#remarksForEndorsement','#remarksEndorsed','#remarksPending','#AddChatbot_answer',
            '#EditChatbot_answer','#additionalText','#additionalText2'];
        summernoteIds.forEach(function(id) {
            if ($(id).length) $(id).summernote();
        });

        // CodeMirror — only init if element exists
        var cmEl = document.getElementById('codeMirrorDemo');
        if (cmEl) {
            CodeMirror.fromTextArea(cmEl, {
                mode: "htmlmixed",
                theme: "monokai"
            });
        }
    })
</script>

</body>

</html>