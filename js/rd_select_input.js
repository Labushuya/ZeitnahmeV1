$("#add_rd").change(function () {
  var numInputs = $(this).val();
  for (var i = 0; i < numInputs; i++)
    $("#add_rd_Area").append('<input name="inputs[]" />');
});