
function wooLockerStartDate(plusDay){
	let date = new Date();
	return new Date(date.setDate(date.getDate() + plusDay));
}

function wooDatePickerOtherOptions(){
	return { 
		closeText: 'Fermer',
		prevText: 'Précédent',
		nextText: 'Suivant',
		currentText: 'Aujourd\'hui',
		monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
		monthNamesShort: ['Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
		dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
		dayNamesShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],
		dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
		weekHeader: 'Sem.',
		dateFormat: 'yy-mm-dd'
	}
}

function dwd(date)
{
	var m = date.getMonth(), d = date.getDate(), y = date.getFullYear();
	//var day = jQuery.datepicker.formatDate('DD', date);
	var day = 'orddd_weekday_' + date.getDay();
        
	if (jQuery("#"+day).val() != 'checked')
	{
		return [false];
	}
	return [true];
}


function chd(date)
{
	//var nW = dwd(date);
	return jQuery.datepicker.noWeekends;
}

function avd(date)
{
	var delay_days = parseInt(jQuery("#minimumOrderDays").val());
	var noOfDaysToFind = parseInt(jQuery("#number_of_dates").val())
	
	if(isNaN(delay_days))
	{
		delay_days = 0;
	}
	if(isNaN(noOfDaysToFind))
	{
		noOfDaysToFind = 1000;
	}
	
	var minDate = delay_days + 1;
	
	var date = new Date();
	var t_year = date.getFullYear();
	var t_month = date.getMonth()+1;
	var t_day = date.getDate();
	var t_month_days = new Date(t_year, t_month, 0).getDate();
	
	var s_day = new Date( ad( date , delay_days ) );
	start = (s_day.getMonth()+1) + "/" + s_day.getDate() + "/" + s_day.getFullYear();
	var start_month = s_day.getMonth()+1;
	var start_year = s_day.getFullYear();
	
	var end_date = new Date( ad( s_day , noOfDaysToFind ) );
	end = (end_date.getMonth()+1) + "/" + end_date.getDate() + "/" + end_date.getFullYear();
	
	var specific_max_date = start;
	var m = date.getMonth(), d = date.getDate(), y = date.getFullYear();
	var currentdt = m + '-' + d + '-' + y;
	
	var dt = new Date();
	var today = dt.getMonth() + '-' + dt.getDate() + '-' + dt.getFullYear();
	
	
	var loopCounter = gd(start , end , 'days');
	var prev = s_day;
	var new_l_end, is_holiday;
	for(var i=1; i<=loopCounter; i++)
	{
		var l_start = new Date(start);
		var l_end = new Date(end);
		new_l_end = l_end;
		var new_date = new Date(ad(l_start,i));

		var day = "";
		day = 'orddd_weekday_' + new_date.getDay();
		day_check = jQuery("#"+day).val();
		
		//alert(day_check);
		if( day_check != "checked")
		{
			new_l_end = l_end = new Date(ad(l_end,1));
			end = (l_end.getMonth()+1) + "/" + l_end.getDate() + "/" + l_end.getFullYear();
			//alert(end);
			diff = gd(l_end , specific_max_date , 'days');
			if (diff >= 0)
			{
				loopCounter = gd(start , end , 'days');
			}
			//alert(loopCounter);
		}
		else
		{
			loopCounter = gd(start , end , 'days');
			//alert(loopCounter);
		}
	}

	
        return {
                minDate: minDate,
        maxDate: l_end
    };
	
}

function ad(dateObj, numDays)
{
	return dateObj.setDate(dateObj.getDate() + numDays);
}

function gd(date1, date2, interval)
{
	var second = 1000,
	minute = second * 60,
	hour = minute * 60,
	day = hour * 24,
	week = day * 7;
	date1 = new Date(date1).getTime();
	date2 = (date2 == 'now') ? new Date().getTime() : new Date(date2).getTime();
	var timediff = date2 - date1;
	if (isNaN(timediff)) return NaN;
		switch (interval) {
		case "years":
			return date2.getFullYear() - date1.getFullYear();
		case "months":
			return ((date2.getFullYear() * 12 + date2.getMonth()) - (date1.getFullYear() * 12 + date1.getMonth()));
		case "weeks":
			return Math.floor(timediff / week);
		case "days":
			return Math.floor(timediff / day);
		case "hours":
			return Math.floor(timediff / hour);
		case "minutes":
			return Math.floor(timediff / minute);
		case "seconds":
			return Math.floor(timediff / second);
		default:
			return undefined;
	}
}

