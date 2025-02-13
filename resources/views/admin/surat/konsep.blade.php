@include('admin.pengaturan_surat.asset_tinymce')
@include('admin.layouts.components.sweetalert2')

@extends('admin.layouts.index')

@section('title')
    <h1>
        Konsep Surat {{ ucwords($surat->nama) }}
    </h1>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('surat') }}">Daftar Cetak Surat</a></li>
    <li class="active"> Surat {{ ucwords($surat->nama) }}</li>
    <li class="active"> Konsep Surat {{ ucwords($surat->nama) }}</li>
@endsection

@section('content')
    @include('admin.layouts.components.notifikasi')

    <div class="box box-info">
        {!! form_open(null, 'id="validasi"') !!}
        <div class="box-body">
            <input type="hidden" id="id_surat" value="{{ $id_surat }}">
            <div class="form-group">
                <textarea name="isi_surat" class="form-control input-sm editor required">{{ $isi_surat }}</textarea>
            </div>
        </div>
        <div class="box-footer text-center">
            <a href="{{ route('surat') }}" id="back"
                class="btn btn-social btn-info btn-sm btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block">
                <i class="fa fa-arrow-circle-left"></i>Kembali ke Daftar Surat
            </a>
            <button type="button" id="cetak-pdf" class="btn btn-social btn-success btn-sm"><i class="fa fa-file-pdf-o"></i>
                Cetak
                PDF</button>
            @if ($tolak != '-1')
                <button type="button" id="draft-pdf"
                    onclick="$('#validasi').attr('action', '{{ $aksi_konsep }}').submit()"
                    class="btn btn-social btn-warning btn-sm"><i class="fa fa-file-code-o"></i>
                    Konsep</button>
                <a href="{{ route('keluar/clear/masuk') }}" id="next" style="display:none"
                    class="btn btn-social btn-info btn-sm btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block">
                    ke Permohonan Surat <i class="fa fa-arrow-circle-right"></i>
                @else
                    <a href="{{ route('keluar/clear/ditolak') }}" id="next" style="display:none"
                        class="btn btn-social btn-info btn-sm btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block">
                        Ke Daftar Surat Ditolak <i class="fa fa-arrow-circle-right"></i>
            @endif

            </a>
        </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        $(function() {
            $('#cetak-pdf').click(function(e) {
                e.preventDefault();
                tinymce.triggerSave();
                Swal.fire({
                    title: 'Membuat Surat..',
                    timerProgressBar: true,
                    didOpen: () => {
                        Swal.showLoading()
                    },
                    allowOutsideClick: () => false
                })
                $.ajax({
                        url: `{{ $aksi_cetak }}`,
                        type: 'POST',
                        xhrFields: {
                            responseType: 'blob'
                        },
                        data: $("#validasi").serialize(),
                        success: function(response, status, xhr) {
                            // https://stackoverflow.com/questions/34586671/download-pdf-file-using-jquery-ajax
                            var filename = "";
                            var disposition = xhr.getResponseHeader('Content-Disposition');

                            if (disposition) {
                                var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                                var matches = filenameRegex.exec(disposition);
                                if (matches !== null && matches[1]) filename = matches[1].replace(
                                    /['"]/g, '');
                            }
                            var linkelem = document.createElement('a');
                            try {
                                var blob = new Blob([response], {
                                    type: 'application/octet-stream'
                                });
                                if (typeof window.navigator.msSaveBlob !== 'undefined') {
                                    //   IE workaround for "HTML7007: One or more blob URLs were revoked by closing the blob for which they were created. These URLs will no longer resolve as the data backing the URL has been freed."
                                    window.navigator.msSaveBlob(blob, filename);
                                } else {
                                    var URL = window.URL || window.webkitURL;
                                    var downloadUrl = URL.createObjectURL(blob);

                                    if (filename) {
                                        // use HTML5 a[download] attribute to specify filename
                                        var a = document.createElement("a");

                                        // safari doesn't support this yet
                                        if (typeof a.download === 'undefined') {
                                            window.location = downloadUrl;
                                        } else {
                                            a.href = downloadUrl;
                                            a.download = filename;
                                            document.body.appendChild(a);
                                            a.target = "_blank";
                                            a.click();
                                        }
                                    } else {
                                        window.location = downloadUrl;
                                    }
                                }
                            } catch (ex) {
                                alert(ex); // This is an error
                            }
                        }
                    })
                    .done(function(response, textStatus, xhr) {
                        if (xhr.status == 200) {
                            $('#cetak-pdf').hide();
                            $('#draft-pdf').hide();
                            $('#back').remove();
                            $('#next').show();
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: 'Surat Selesai Dibuat',
                                showConfirmButton: false,
                                timer: 1500
                            })
                        }
                    })
                    .fail(function(error) {
                        Swal.fire({
                            icon: 'error',
                            text: error.statusText,
                        })
                    });
            });
        });
    </script>
@endpush
