$(document).ready(function() {
	var oTable = $('#users_table').dataTable({
		"aaSorting": [[ 0, "asc" ]],
		"bFilter": true,
		"iDisplayLength":100
	});
     
    /* Apply the jEditable handlers to the table */
    oTable.$('td').not('.exclude').editable( '../api/user/', {
        "callback": function( sValue, y ) {
            var aPos = oTable.fnGetPosition( this );
            oTable.fnUpdate( sValue, aPos[0], aPos[1] );
        },
        "submitdata": function ( value, settings ) {
            return {
                "action": "update",
                "row_id": this.parentNode.getAttribute('id'),
                "column": oTable.fnGetPosition( this )[2]
            };
        },
        "width": "100%"
    } );

	$("#btnAddUser").click(function(){
		$.ajax({   
			type: "POST",
			data : $("#frmAddUser").serialize(),
			cache: false,  
			url: "../api/user/",   
			success: function(data){
                            }   
                        });   
	});

        $(".btn-delete").click(function(){
		$.ajax({   
			type: "POST",
			data : {"action":"delete","id":$(this).attr("data-id")},
			cache: false,  
			url: "../api/user/",   
			success: function(data){
				oTable.fnDeleteRow($(this).parents('tr')[0]);
                            }   
                        });   
	});
});
