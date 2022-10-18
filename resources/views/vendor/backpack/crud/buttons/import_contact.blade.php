<!-- Button trigger modal -->
<button type="button" class="btn btn-primary modal_close" data-toggle="modal" data-target="#exampleModal123">
  <i class="la la-cloud-upload"></i> Import Contact
</button>

<!-- Modal -->
<div class="modal fade" id="exampleModal123" data-backdrop="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="close modal_close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="importContactForm" enctype='multipart/form-data'>
          @csrf
          <div class="input-group">
            <div class="custom-file">
              <input type="file" name="file" class="custom-file-input" id="contactFileUpload">
              <label id="contactCustomField" class="custom-file-label" for="contactFileUpload">Choose file</label>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer md-align">
        <a href="{{ asset('file/Sample Contact.xlsx') }}" download class=" mg-right"><button type="button" class="btn btn-primary"><i class="la la-cloud-download"></i> Sample File</button></a>
        <button type="button" class="btn btn-secondary modal_close" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="import_contacts">Import</button>
      </div>
    </div>
  </div>
</div>

<style type="text/css">
  .mg-right{
        margin-right: auto !important;
  }
</style>

<script src="{{ asset('js/jquery-1.9.1.js') }}"></script>
<script type="text/javascript">
   $("#import_contacts").click(function(evt){   

      evt.preventDefault();
      var formData = new FormData($("#importContactForm")[0]);
      var URL = "{{ route('importContact') }}";
      console.log(URL);

      $.ajax({
          url: URL,
          type: 'POST',
          data: formData,
          async: false,
          cache: false,
          contentType: false,
          enctype: 'multipart/form-data',
          processData: false,
          success: function (response) {
            console.log(response)
            if (response.message) {
              NotyToaster('success', response.message)
            }
            $(".modal_close").trigger("click");
            $('#contactFileUpload').val('');
            $('#contactCustomField').html('Choose file');
            setTimeout(function() {
                location.reload();
            }, 3000);
          },
          error: function (err) {
            console.log(err);
            if (err.status == 422) {
              if (err.responseJSON.errors.file[0]) {
                NotyToaster('error', err.responseJSON.errors.file[0])
              } else {
                NotyToaster('error', 'Oops, Something went wrong!')
              }
            } else {
              if (err.responseJSON.message) {
                NotyToaster('error', err.responseJSON.message)
              } else {
                NotyToaster('error', 'Oops, Something went wrong!')
              }
              $('#contactFileUpload').val('');
              $('#contactCustomField').html('Choose file');
              $(".modal_close").trigger("click");
            }
          }
       });

       return false;

    });

    $('#contactFileUpload').on('change',function(){
        var fileName = $(this).val();
        console.log(fileName);
        $('#contactCustomField').html(fileName);
    })

    $('.modal_close').on('click',function(){
        $('#contactFileUpload').val('');
        $('#contactCustomField').html('Choose file');
    })

    function NotyToaster($type = 'warning', $msg = '') {
      Noty.overrideDefaults({
          layout: 'topRight',
          theme: 'backstrap',
          timeout: 3000,
          closeWith: ['click', 'button'],
      });
      
      let alert = {};
      alert['type'] = $type;
      alert['text'] = $msg;

      new Noty(alert).show();

    }
</script>