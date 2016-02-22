function isValidDate(dateStr) 
{
    var datePat = /^(\d{1,2})(\/|-)(\d{1,2})\2(\d{2}|\d{4})$/;

    var datePat = /^(\d{1,2})(\/|-)(\d{1,2})\2(\d{4})$/;

    var matchArray = dateStr.match(datePat); // is the format ok?
    if (matchArray == null) {
        return 1;
    }

    month = matchArray[1]; // parse date into variables
    day = matchArray[3];
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

function CheckDate(start,end)
{
    var cStart;
    var cEnd;
    var err;
    
    cStart = isValidDate(start);
    if (cStart == 0) {
        cEnd = isValidDate(end);
        if(cEnd == 0)  return true;
        else err = cEnd;
    } else err = cStart;


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
        alert("February wrong");
        return false;
    } else return true;

}

