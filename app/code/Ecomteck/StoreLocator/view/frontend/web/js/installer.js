define(["jquery"], function ($) {
  $(document).ajaxComplete(function (event, xhr, settings) {
    var excludeDays = ["2024-06-16", "2024-06-16", "2024-06-17"];
    $(".storePickupdatepicker").datepicker({
      showMonthAfterYear: false,
      dateFormat: "mm/dd/yy",
      changeMonth: true,
      changeYear: true,
      /* yearRange: '2020:2022', */
      minDate: 0,
      beforeShowDay: function (date) {
        var staticDate = new Date(2024, 5, 16);
        if (
          date.getFullYear() === staticDate.getFullYear() &&
          date.getMonth() === staticDate.getMonth() &&
          date.getDate() === staticDate.getDate()
        ) {
          return [false, "disabled", "This date is disabled"];
        }
        var day = date.getDay();
        /* start extra adding to disable today if time is passed 5 PM */
        var currentDate = new Date();
        var currentHour = currentDate.getHours();
        var currentMinutes = currentDate.getMinutes();
        var isAfter5PM =
          currentHour > 17 || (currentHour === 17 && currentMinutes > 0);
        // Check if the date is today and if the current time is after 5 PM
        if (date.toDateString() === currentDate.toDateString() && isAfter5PM) {
          //console.log("Today is disabled after 5 PM");
          return [false, "disabled", "Today is disabled after 5 PM"];
        }
        /* end extra adding to disable today if time is passed 5 PM */
        return [day == 0 ? true : disableSpecificDate(date), ""];
      },
    });

    function disableSpecificDate(date) {
      // To disable specific day
      var dateArr = [
        String(date.getFullYear()),
        String(date.getMonth() + 1),
        String(date.getDate()),
      ];
      if (dateArr[1].length == 1) dateArr[1] = "0" + dateArr[1];
      if (dateArr[2].length == 1) dateArr[2] = "0" + dateArr[2];
      return excludeDays.indexOf(dateArr.join("-")) == -1;
    }

    $(".storePickupdatepicker").change(function () {
      var currentDate = new Date();
      var currentHour = currentDate.getHours();
      var currentMinutes = currentDate.getMinutes();
      var isAfter12PM =
        currentHour > 12 || (currentHour === 12 && currentMinutes > 0);

      var storeTimeId = jQuery(this).attr("data-storeid");
      var $timePicker = $(
        '.storePickuptimepicker[data-storetimeid="' + storeTimeId + '"]'
      );

      if (
        this.value &&
        this.value === currentDate.toLocaleDateString() &&
        isAfter12PM
      ) {
        // Today and after 12 PM, remove the "AM" option for the specific time picker
        $timePicker.find('option[value="AM"]').remove();
      } else {
        // Not today or before 12 PM, add the "AM" option if it doesn't exist
        if ($timePicker.find('option[value="AM"]').length === 0) {
          $timePicker.append('<option value="AM">AM</option>');
        }
      }
      if (this.value == "06/18/2024") {
        $timePicker.find('option[value="AM"]').remove();
      }
    });

    /* jQuery('.storePickupdatepicker').change(function(){ 
			    var selecteddate=jQuery(this).val();
			    var selecctedday=jQuery(this).datepicker('getDate').getDay();
			    var storeid=jQuery(this).attr('data-storeid');
			    jQuery('select[data-storetimeid='+storeid+']').empty();

			    selecteddate=selecteddate.replace("/", "");
			    selecteddate=selecteddate.replace("/", "");
			    todaydate=todaydate.replace("/", "");
			    todaydate=todaydate.replace("/", "");
			     
			    var storeStartEnddate=allStoreOpeningdatetimep[storeid][selecctedday];
			    
			    jQuery.each(alldatetime, function(key,value) { 
		       	    var valueKey=key+value;
		       	    valueKey=value;
		       	    
		       	    if(typeof storeStartEnddate[0] !== "undefined" && typeof storeStartEnddate[1] !== "undefined"){ 
				       	    var storeStartdate=storeStartEnddate[0];
				       	    var storeEndDate=storeStartEnddate[1];
				       	    key=key.replace(".", "");
				       	    storeStartdate=storeStartdate.replace(":", "");
				       	    storeEndDate=storeEndDate.replace(":", "");
				       	    storeStartdate=storeStartdate.replace(/\"/g, "");
				            storeEndDate=storeEndDate.replace(/\"/g, "");
				       		
				       	   if(key >= storeStartdate && key <= storeEndDate){ 	
				       	   
				       	    	    if(key <= todayhrssecond && (selecteddate == todaydate) ) 
				       	    	    {
					       	  //   	    jQuery('select[data-storetimeid='+storeid+']').append(jQuery('<option>', {
									      //      value: valueKey,
										     //   text: valueKey,
										     //  disabled  : 'disabled',
									      // }));	
								     
				       	    	    }else{ 
									    	jQuery('select[data-storetimeid='+storeid+']').append(jQuery('<option>', {
										         value: valueKey,
											     text: valueKey,
										     }));	
							        } 	
						    }else{
						    		// jQuery('select[data-storetimeid='+storeid+']').append(jQuery('<option>', {
							     //     value: valueKey,
								    //  text: valueKey,
								    //  disabled  : 'disabled',
							     // }));	
						    } 	
					 }else{
					 		    // jQuery('select[data-storetimeid='+storeid+']').append(jQuery('<option>', {
							     //     value: valueKey,
								    //  text: valueKey,
								    //  disabled  : 'disabled',
							     // }));	
					 }	    

			    });
	    
    	    }); */
  });
});
