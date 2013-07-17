$(document)
		.ready(
				function() {
					var oTable = $('#records_table').dataTable({
						"aaSorting" : [ [ 0, "desc" ] ],
						"bFilter" : true,
						"iDisplayLength" : 100
					});

					/* Apply the jEditable handlers to the table */
					oTable
							.$('td.editable')
							.editable(
									'api/machine_time/',
									{
										type : 'select',
										"data" : " {'unknown':' ','ABS':'ABS','Acrylic':'Acrylic','Cardboard':'Cardboard','Fabric':'Fabric','Foam':'Foam','Food':'Food', 'Paper':'Paper','Rubber':'Rubber','Wood':'Wood'}",
										callback : function(sValue, y) {
											var aPos = oTable.fnGetPosition(this);
											oTable.fnUpdate(sValue, aPos[0], aPos[1]);
										},
										submitdata : function(value, settings) {
											return {
												"action" : "update_material",
												"row_id" : this.parentNode.getAttribute('data-id'),
												"column" : oTable.fnGetPosition(this)[2]
											};
										},
										width : "100%",
										onblur : 'submit',
										tooltip : 'click to edit'
									});
					$('#user_sum_table').dataTable({
						"aaSorting" : [ [ 0, "desc" ] ],
						"bFilter" : false,
						"bLengthChange" : false
					});

					var options = {
						lines: {
							show: true
						},
						points: {
							show: true
						},
						xaxis: {
							tickDecimals: 0,
							tickSize: 1
						}
					};

					var data = [];
					$.plot("#usage_chart", data, options);
					// Fetch one series, adding to what we already have

					var alreadyFetched = {};

					$("button#btn-previous").click(function () {

						var button = $(this);

						// Find the URL in the link right next to us, then fetch the data

						var dataurl = "api/machine_time";

						function onDataReceived(series) {

							// Extract the first coordinate pair; jQuery has parsed it, so
							// the data is now just an ordinary JavaScript object

							var firstcoordinate = "(" + series.data[0][0] + ", " + series.data[0][1] + ")";
							button.siblings("span").text("Fetched " + series.label + ", first point: " + firstcoordinate);

							// Push the new data onto our existing data array

							if (!alreadyFetched[series.label]) {
								alreadyFetched[series.label] = true;
								data.push(series);
							}

							$.plot("#placeholder", data, options);
						}

						$.ajax({
							url: dataurl,
							type: "GET",
							dataType: "json",
							success: onDataReceived
						});
					});

					// Initiate a recurring data update

					$("button#btn-previous").click(function () {

						data = [];
						alreadyFetched = {};

						$.plot("#placeholder", data, options);

						var iteration = 0;

						function fetchData() {

							++iteration;

							function onDataReceived(series) {

								// Load all the data in one pass; if we only got partial
								// data we could merge it with what we already have.

								data = [ series ];
								$.plot("#placeholder", data, options);
							}

							// Normally we call the same URL - a script connected to a
							// database - but in this case we only have static example
							// files, so we need to modify the URL.

							$.ajax({
								url: "data-eu-gdp-growth-" + iteration + ".json",
								type: "GET",
								dataType: "json",
								success: onDataReceived
							});

							if (iteration < 5) {
								setTimeout(fetchData, 1000);
							} else {
								data = [];
								alreadyFetched = {};
							}
						}

						setTimeout(fetchData, 1000);
					});

					// Load the first series by default, so we don't have an empty plot
					$("button.fetchSeries:first").click();
				});