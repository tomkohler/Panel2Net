/*
    This loads text from [URLTOLOAD] in between [DELIMITER_1] and [DELIMITER_2]
    If [DELIMITER_1] and [DELIMITER_2] is not found, Title shows the whole text.
    An optional [UPDATEINTERVAL] in milliseconds may also be set to auto-update.
    The script will check the local file for any changes in text every [UPDATEINTERVAL] milliseconds
    which will automatically update the text within the stage.
    Setting it to 0 or lower disables auto-update.
*/

/**
 * @name URLTOLOAD
 * @label Url to Load
 * @type text
 * @description The web address/url from where to extract text
 */
var URLTOLOAD = "http://46.101.186.145/abcd/TESTNEUC-lastaction.xml";

/**
 * @name DELIMETER_1
 * @label Delimeter 1
 * @type text
 * @description The text, which when found signals the start of extracting text. If not found, script will show the whole text.
 */
var DELIMETER_1 = "<TeamFoulB>";

/**
 * @name DELIMETER_2
 * @label Delimeter 2
 * @type text
 * @description The text, which when found signals the end of extracting. text If not found, script will show the whole text.
 */
var DELIMETER_2 = "</TeamFoulB>";

/**
 * @name UPDATEINTERVAL
 * @label Update Interval
 * @type int
 * @positiveOnly true 
 * @description Optional. If set, the script will check the local file for any changes in text every X seconds, which will automatically update the text within the stage.
 */
var UPDATEINTERVAL = 1;

/**
 * @name LINEBREAK_BEHAVIOR
 * @label Behavior on Line Breaks
 * @type select
 * @options Preserve Line Breaks||Ignore Line Breaks||Replace
 * @description Preserve Line Breaks = Line breaks will be preserved, and text will be wrapped to the next line||Ignore Line Breaks = Line breaks will simply be omitted, and without spacing. Text will be displayed on single line||Replace = Replace line breaks with a specific character or set of characters
 */
var LINEBREAK_BEHAVIOR = "Replace";

/**
 * @name REPLACE_WITH
 * @label Replace Line Break With
 * @type text
 * @description The text to replace line breaks with, if Replace is selected as line break behavior
 */
var REPLACE_WITH = " ";

/*Do not modify anything below*/

var oldResponse;

function GetTextFromRemote()
{
    $.ajax({url: URLTOLOAD,
    type: "GET",
    dataType: "text",
    complete: function()
    {
        if (UPDATEINTERVAL > 0)
            smlTitleTimeouts = setTimeout(function(){GetTextFromRemote(); }, UPDATEINTERVAL*1000);
    },
    success: function(response)
    {
        var responseCleaned;
        if (LINEBREAK_BEHAVIOR == "Ignore Line Breaks")
        {
            responseCleaned = response.replace(/(\r\n|\n|\r)/gm,"");
        }
        else if (LINEBREAK_BEHAVIOR == "Replace")
        {
            responseCleaned = response.replace(/(\r\n|\n|\r)/gm, REPLACE_WITH);
        }
        else
        {
            responseCleaned = response.replace(/(\r\n|\n|\r)/gm,"<br>");
        }
        
        if (DELIMETER_1 != "" && DELIMETER_2 != "")
        {
            var responseCleanedLength = responseCleaned.length;
            var indexOfDelim1 = responseCleaned.indexOf(DELIMETER_1) > -1 ? (responseCleaned.indexOf(DELIMETER_1) + DELIMETER_1.length) : 0;
            var substringResponseCleaned = responseCleaned.substring(indexOfDelim1);
            var substringResponseCleanedLength = substringResponseCleaned.length;

            var initialIndexOfDelim2 = substringResponseCleaned.indexOf(DELIMETER_2) > -1 ? substringResponseCleaned.indexOf(DELIMETER_2) : responseCleanedLength;
            var indexOfDelim2 = responseCleanedLength;
            if (initialIndexOfDelim2 != responseCleanedLength)
            {
                indexOfDelim2 = initialIndexOfDelim2 + (responseCleanedLength - substringResponseCleanedLength);
            }

            if (indexOfDelim2 <= indexOfDelim1)
                indexOfDelim2 = responseCleaned.length - 1;
            responseCleaned = responseCleaned.substring(indexOfDelim1, indexOfDelim2);
        }

        if(oldResponse!=responseCleaned)
        {
        SetText(responseCleaned, "Remote URL: " + URLTOLOAD);
        }
        oldResponse=responseCleaned;
    }});
}

if (smlTitleTimeouts && smlTitleTimeouts != null)
    {clearTimeout(smlTitleTimeouts);}
GetTextFromRemote();