var monthName = new Array("","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September",
				"Oktober","Nopember","Desember");

function MatchDate(dateStr)
{
    var datePat = /^(\d{1,2})(\/|-)(\d{1,2})\2(\d{4})$/;
    var matchArray = dateStr.match(datePat); // is the format ok?

	if (matchArray == null) return null;
	else return matchArray;
}

function isValidDate(dateStr) 
{
	var matchArray = MatchDate(dateStr);
	if (matchArray == null) {
        return 1;
    }

    month = matchArray[3]; // parse date into variables
    day = matchArray[1];
    year = matchArray[4];
    
    if (month < 1 || month > 12) { // check month range
        return 2;
    }
    if (day < 1 || day > 31) {
        return 3;
    }
    if ((month==4 || month==6 || month==9 || month==11) && day==31) {
        return 4
    }
    if (month == 2) { // check for february 29th
        var isleap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));
        if (day>29 || (day==29 && !isleap)) {
            alert("February " + year + " doesn't have " + day + " days!");
            return 5;
        }
    }
    return 0;  // date is valid
}

function CheckDate(tgl)
{
    var err;
    if(!tgl) return true;

    err = isValidDate(tgl);

    if(err == 1){
        alert('Date Format Missmatch');
        return false;
    } else if (err == 2) {
        alert("Month must be between 1 and 12.");
        return false;
    } else if (err == 3) {
        alert("Day must be between 1 and 31.");
        return false;
    } else if (err == 4) {
        alert("Month doesn't have 31 days!");
        return false;
    } else if (err == 5) {
        return false;
    } else return true;
}

function CompareDate(start,end)
{
	var matchStart = MatchDate(start);
	var matchEnd = MatchDate(end);
	var dStart,dEnd;
	var msStart, msEnd, msDiff;

	if(!matchStart || !matchEnd)  // -- return 0 --> ada yg null
		return 0;
	
	dStart = new Date(matchStart[4],matchStart[3],matchStart[1]);
	dEnd = new Date(matchEnd[4],matchEnd[3],matchEnd[1]);

    msStart = dStart.getTime();
    msEnd = dEnd.getTime();

	if(msStart == msEnd)  //  -- return 1 --> podo
		return 1;
	else if(msStart > msEnd)  // -- return 2 --> start lbh besar dr end
		return 2;
	else if(msStart < msEnd)  // -- return 3 --> start lbh kecil dr end
		return 3;		
}

// --- param date dd-mm-yyyy
function FormatDateLong(tgl)
{
     var splitDate = MatchDate(tgl);
     
     return splitDate[1] + ' ' + monthName[eval(splitDate[3])] + ' ' + splitDate[4];
}
