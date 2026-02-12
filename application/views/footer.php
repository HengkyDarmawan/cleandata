

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="login.html">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="<?= base_url('assets/')?>vendor/jquery/jquery.min.js"></script>
    <script src="<?= base_url('assets/')?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>


    <!-- Core plugin JavaScript-->
    <script src="<?= base_url('assets/')?>vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?= base_url('assets/')?>js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="<?= base_url('assets/')?>vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url('assets/')?>vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script>
        $(function () {

            if ( ! $.fn.DataTable.isDataTable('#dataTable') ) {

                var table = $('#dataTable').DataTable({
                    pageLength: 10,
                    destroy: true,
                    ordering: true,
                    searching: true,
                    lengthChange: true,
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ - _END_ dari _TOTAL_",
                        paginate: { next: "›", previous: "‹" }
                    }
                });

                let selected = new Set();

                function sync() {
                    table.rows().every(function () {
                        var cb = $(this.node()).find('.check-item');
                        if (cb.length) cb.prop('checked', selected.has(cb.val()));
                    });

                    $('#count-selected').text(selected.size);
                    $('#selected-info').toggle(selected.size > 0);
                }

                $('#dataTable tbody').on('change', '.check-item', function () {
                    var id = $(this).val();
                    this.checked ? selected.add(id) : selected.delete(id);
                    sync();
                });

                $('#check-all').on('change', function () {
                    var checked = this.checked;
                    table.rows({ search: 'applied' }).every(function () {
                        var cb = $(this.node()).find('.check-item');
                        if (!cb.length) return;
                        checked ? selected.add(cb.val()) : selected.delete(cb.val());
                        cb.prop('checked', checked);
                    });
                    sync();
                });

                table.on('draw', sync);

                $('#formMaster').on('submit', function () {
                    if (selected.size === 0) {
                        alert('Pilih minimal satu barang!');
                        return false;
                    }

                    $(this).find('input[name="selected_ids[]"]').remove();
                    selected.forEach(id => {
                        $(this).append(
                            $('<input>').attr({
                                type: 'hidden',
                                name: 'selected_ids[]',
                                value: id
                            })
                        );
                    });

                    return confirm('Proses ' + selected.size + ' barang?');
                });

            }

        });
        </script>

</body>

</html>