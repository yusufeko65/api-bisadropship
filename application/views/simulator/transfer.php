
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <label>Tanggal Transfer</label>
    <input class="myText" type="text" value="<?= date('d-m-Y') ;?>" disabled> 

    <br>

    <label>Value</label>
    <input class="myText" id="value" type="number"> 

    <br>

    
    <button class="btn btn-primary" data-target="#exampleModalLong" data-toggle="modal" onclick="submitText()" type="button"> 
      Transfer
    </button>

    <div id="json-result" style="margin-top:30px;"><div>

    <script>
        function submitText(){
            var nominal = $('#value').val();

            $.ajax({
                url: "simulator/transfer",
                type: "POST",
                data: "data=" + nominal, 
                success: function(result){
                    $("#json-result").html(result);
                }
            });
        }
    </script>