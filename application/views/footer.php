

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
            $(document).ready(function () {

                var table = $('#dataTable').DataTable({
                    pageLength: 10,
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

                let cart = new Map();

                function renderCart() {
                    let html = '';
                    let grand = 0;

                    cart.forEach(item => {

                        let q = parseInt(item.qty) || 0;
                        let m = parseFloat(item.modal) || 0;

                        let subtotal = q * m;
                        grand += subtotal;

                        html += `
                        <li class="list-group-item p-2">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <b>${item.kode}</b><br>
                                    <small class="text-muted">${item.nama}</small>
                                </div>
                                <button type="button"
                                        class="btn btn-sm btn-danger remove-item"
                                        data-id="${item.id}">×</button>
                            </div>

                            <div class="d-flex align-items-center mt-2">
                                <input type="number"
                                    class="form-control form-control-sm qty-edit mr-1"
                                    data-id="${item.id}"
                                    value="${q}"
                                    min="0"
                                    style="width:70px">

                                <input type="number"
                                    class="form-control form-control-sm price-edit mr-1"
                                    data-id="${item.id}"
                                    value="${m}"
                                    min="0"
                                    style="width:110px">

                                <small class="ml-auto font-weight-bold">
                                    Rp ${subtotal.toLocaleString()}
                                </small>
                            </div>
                        </li>`;
                    });

                    $('#cart-list').html(html || '<li class="list-group-item text-center text-muted">Kosong</li>');
                    $('#cart-total').text('Rp ' + grand.toLocaleString());

                    let ticketPrice = parseInt($('#ticket-price').val()) || 0;
                    let est = ticketPrice > 0 ? Math.floor(grand / ticketPrice) : 0;
                    $('#ticket-estimate').text(est.toLocaleString());
                }
                // $(document).on('input', '.qty-edit', function () {
                //     let id = String($(this).data('id'));
                //     let val = parseInt($(this).val()) || 0;

                //     if (cart.has(id)) {
                //         let obj = cart.get(id);
                //         obj.qty = val;
                //         cart.set(id, obj);
                //         renderCart();
                //     }
                // });
                $(document).on('input', '.qty-edit', function () {
                    let id = String($(this).data('id'));
                    let val = parseInt($(this).val());

                    if (!isNaN(val) && cart.has(id)) {
                        let obj = cart.get(id);
                        obj.qty = val;
                        cart.set(id, obj);
                        renderCart();
                    }
                });


                // $(document).on('input', '.price-edit', function () {
                //     let id = String($(this).data('id'));
                //     let val = parseFloat($(this).val()) || 0;

                //     if (cart.has(id)) {
                //         let obj = cart.get(id);
                //         obj.modal = val;
                //         cart.set(id, obj);
                //         renderCart();
                //     }
                // });
                $(document).on('input', '.price-edit', function () {
                    let id = String($(this).data('id'));
                    let val = parseFloat($(this).val());

                    if (!isNaN(val) && cart.has(id)) {
                        let obj = cart.get(id);
                        obj.modal = val;
                        cart.set(id, obj);
                        renderCart();
                    }
                });

                $('#ticket-price').on('input', renderCart);

                $('#toggle-cart').on('click', function () {
                    $('#selected-info').toggleClass('collapsed');

                    if ($('#selected-info').hasClass('collapsed')) {
                        $(this).text('Show');
                    } else {
                        $(this).text('Hide');
                    }
                });

                // checkbox klik
                $('#dataTable').on('change', '.check-item', function () {
                    let cb = $(this);
                    let id = String(cb.val());

                    if (cb.is(':checked')) {

                        if (!cart.has(id)) {
                            cart.set(id, {
                                id: id,
                                kode: cb.data('kode'),
                                nama: cb.data('nama'),
                                qty: parseInt(cb.data('qty')) || 1,   // default minimal 1
                                modal: parseFloat(cb.data('modal')) || 0
                            });
                        }

                    } else {
                        cart.delete(id);
                    }

                    renderCart();
                });

                // check all
                $('#check-all').on('change', function () {
                    let checked = this.checked;

                    $('#dataTable .check-item').each(function () {
                        let cb = $(this);
                        let id = cb.val();

                        cb.prop('checked', checked);

                        if (checked) {
                            if (!cart.has(id)) {
                                cart.set(id, {
                                    id: id,
                                    kode: cb.data('kode'),
                                    nama: cb.data('nama'),
                                    qty: parseInt(cb.data('qty')),
                                    modal: parseFloat(cb.data('modal'))
                                });
                            }
                        } else {
                            cart.delete(id);
                        }
                    });
                    renderCart();
                });

                // hapus dari cart
                $('#cart-list').on('click', '.remove-item', function () {
                    let id = String($(this).data('id'));
                    cart.delete(id);
                    $('#dataTable .check-item[value="' + id + '"]').prop('checked', false);
                    renderCart();
                });

                // submit
                $('#formMaster').on('submit', function () {
                    if (cart.size === 0) {
                        alert('Pilih minimal satu barang!');
                        return false;
                    }

                    $(this).find('input[name^="items"]').remove();

                    cart.forEach(item => {
                        $(this).append(`
                            <input type="hidden" name="items[${item.id}][qty]" value="${item.qty}">
                            <input type="hidden" name="items[${item.id}][modal]" value="${item.modal}">
                        `);
                    });

                    return confirm('Proses ' + cart.size + ' barang ke event?');
                });

            });
            </script>

</body>

</html>