function loadList()
{
    $.get(
        "index.php?jobs", function( data ) {
            var arrayLength = data.jobs.length;
            if (arrayLength==0) {
                $('#dlprogress').html("<tr><td colspan=\"4\">No downloads in progress.</td></tr>");
            } else {
                var htmlString = [];
                var liString = "";
                $('#dlprogress').html("");
                for (var i = 0; i < arrayLength; i++) {
                    liString = '<i class=\"fa fa-video-camera\"></i>';
                    if (data.jobs[i].type == "audio") {
                        liString = '<i class=\"fa fa-music\"></i>';
                    }
                    htmlString.push("<tr>");
                    htmlString.push("<td style=\"vertical-align: middle;\">"+data.jobs[i].site+"</td>");
                    htmlString.push("<td style=\"vertical-align: middle;\">"+liString+" "+data.jobs[i].file);
                    var urls = data.jobs[i].url.split(",");
                    var urlcount = urls.length;
                    for (var j = 0; j < urlcount; j++) {
                        htmlString.push("<br /><a href=\""+urls[j]+"\">"+urls[j]+"</a>");
                    }
                    htmlString.push("</td>");
                    htmlString.push("<td style=\"vertical-align: middle;\">"+data.jobs[i].status+"</td>");
                    htmlString.push("<td style=\"vertical-align: middle;\">");
                    htmlString.push("<div class=\"btn-group\">");
                    htmlString.push("<a style=\"width: 100px;\" data-href=\"?kill="+data.jobs[i].pid+"\" data-toggle=\"modal\" data-target=\"#confirm-delete\" class=\"btn btn-danger btn-xs\">Stop</a>");
                    htmlString.push("</div>");
                    htmlString.push("</tr>");
                }
                htmlString.push("<tr>");
                htmlString.push("<td></td>");
                htmlString.push("<td></td>");
                htmlString.push("<td></td>");
                htmlString.push("<td>");
                htmlString.push("<div class=\"btn-group\">");
                htmlString.push("<button id=\"killallbutton\" style=\"width: 100px;\" class=\"btn btn-danger btn-xs\" data-href=\"?kill=all\" data-toggle=\"modal\" data-target=\"#confirm-delete\">");
                htmlString.push("Stop All");
                htmlString.push("</button>");
                htmlString.push("</div>");
                htmlString.push("</td>");
                htmlString.push("</tr>");
                $('#dlprogress').html(htmlString.join("\n"));
            }
    
            arrayLength = data.queue.length;
            if (arrayLength==0) {
                $('#dlqueue').html("<tr><td colspan=\"3\">No queued downloads.</td></tr>");
            } else {
                var htmlString = [];
                var liString = "";
                $('#dlqueue').html("");
                for (var i = 0; i < arrayLength; i++) {
                    liString = "";
                    liString = '<i class=\"fa fa-video-camera\"></i>';
                    if (data.queue[i].type == "audio") {
                        liString = '<i class=\"fa fa-music\"></i>';
                    }
                    htmlString.push("<tr>");       
                    htmlString.push("<td style=\"vertical-align: middle;\">");
                    var urls = data.queue[i].url.split(",");
                    var urlcount = urls.length;
                    for (var j = 0; j < urlcount; j++) {
                        htmlString.push("<br /><a href=\""+urls[j]+"\">"+liString+" "+urls[j]+"</a>");
                    }
                    htmlString.push("</td>");
                    htmlString.push("<td style=\"vertical-align: middle;\">"+data.queue[i].dl_format+"</td>");
                    htmlString.push("<td style=\"vertical-align: middle;\">");
                    htmlString.push("<div class=\"btn-group\">");
                    var buttonWidth = "160px;";
                    htmlString.push("<a style=\"width: 160px\" data-href=\"?removeQueued="+data.queue[i].pid+"\" data-toggle=\"modal\" class=\"btn btn-danger btn-xs\" data-target=\"#confirm-delete\">Remove</a>");
                    htmlString.push("</div>");
                    htmlString.push("</td>");
                    htmlString.push("</tr>");       
                }
                htmlString.push("<tr>");
                htmlString.push("<td></td>");
                htmlString.push("<td></td>");
                htmlString.push("<td>");
                htmlString.push("<div class=\"btn-group\">");
                htmlString.push("<button id=\"clearallbutton\" style=\"width: 160px;\" class=\"btn btn-danger btn-xs\" data-href=\"?clear=queue\" data-toggle=\"modal\" data-target=\"#confirm-delete\">");
                htmlString.push("Remove All");
                htmlString.push("</button>");
                htmlString.push("</div>");
                htmlString.push("</td>");
                htmlString.push("</tr>");
                $('#dlqueue').html(htmlString.join("\n"));
            }
            
            arrayLength = data.finished.length;
            if (arrayLength==0) {
                $('#dlcompleted').html("<tr><td colspan=\"4\">No completed downloads on record.</td></tr>");
            } else {
                var htmlString = [];
                var liString = "";
                $('#dlcompleted').html("");
                for (var i = 0; i < arrayLength; i++) {
                    liString = "";
                    if (data.finished[i].type == "audio") {
                        liString = '<i class=\"fa fa-music\"></i>';
                    } else if (data.finished[i].type == "video") {
                        liString = '<i class=\"fa fa-video-camera\"></i>';
                    }
                    htmlString.push("<tr>");       
                    htmlString.push("<td style=\"vertical-align: middle;\">"+data.finished[i].site+"</td>");
                    htmlString.push("<td style=\"vertical-align: middle;\">"+liString+" "+data.finished[i].file);
                    var urls = data.finished[i].url.split(",");
                    var urlcount = urls.length;
                    for (var j = 0; j < urlcount; j++) {
                        htmlString.push("<br /><a href=\""+urls[j]+"\">"+urls[j]+"</a>");
                    }
                    htmlString.push("</td>");
                    htmlString.push("<td style=\"vertical-align: middle;\">"+data.finished[i].status+"</td>");
                    htmlString.push("<td style=\"vertical-align: middle;\">");
                    htmlString.push("<div class=\"btn-group\">");
                    var buttonWidth = "80px;";
                    if (data.logURL != "") {
                        buttonWidth = "60px;";
                        htmlString.push("<a href=\""+data.logURL+"/"+data.finished[i].pid+"\" style=\"width: 40px;\" target=\"_youtubedl_log\" class=\"btn btn-default btn-xs\">Log</a>");
                    }
                    htmlString.push("<a style=\"width: "+buttonWidth+"\" href=\"?restart="+data.finished[i].pid+"\" class=\"btn btn-success btn-xs\">Restart</a>");
                    htmlString.push("<a style=\"width: "+buttonWidth+"\" data-href=\"?clear="+data.finished[i].pid+"\" data-toggle=\"modal\" data-target=\"#confirm-delete\" class=\"btn btn-danger btn-xs\">Remove</a>");
                    htmlString.push("</div>");
                    htmlString.push("</td>");
                    htmlString.push("</tr>");       
                }
                htmlString.push("<tr>");
                htmlString.push("<td></td>");
                htmlString.push("<td></td>");
                htmlString.push("<td></td>");
                htmlString.push("<td>");
                htmlString.push("<div class=\"btn-group\">");
                htmlString.push("<button id=\"clearallbutton\" style=\"width: 160px;\" class=\"btn btn-danger btn-xs\" data-href=\"?clear=recent\" data-toggle=\"modal\" data-target=\"#confirm-delete\">");
                htmlString.push("Remove All");
                htmlString.push("</button>");
                htmlString.push("</div>");
                htmlString.push("</td>");
                htmlString.push("</tr>");
                $('#dlcompleted').html(htmlString.join("\n"));
            }
    
            var arrayLength = data.videos.length;
            if (arrayLength==0) {
                $('#videofiles').html("<tr><td colspan=\"3\">No videos.</td></tr>");
            } else {
                var htmlString = [];
                $('#videofiles').html("");
                for (var i = 0; i < arrayLength; i++) {
                    htmlString.push("<tr>");
                    htmlString.push("<td>"+data.videos[i].file+"</td>");
                    htmlString.push("<td>"+data.videos[i].size+"</td>");
                    htmlString.push("<td>"+data.videos[i].deleteurl+"</td>");
                    htmlString.push("</tr>");
                }
                $('#videofiles').html(htmlString.join("\n"));
                $('#searchDownloadedVideos').keyup();
            }
    
            var arrayLength = data.music.length;
            if (arrayLength==0) {
                $('#musicfiles').html("<tr><td colspan=\"3\">No music.</td></tr>");
            } else {
                var htmlString = [];
                $('#musicfiles').html("");
                for (var i = 0; i < arrayLength; i++) {
                    htmlString.push("<tr>");
                    htmlString.push("<td>"+data.music[i].file+"</td>");
                    htmlString.push("<td>"+data.music[i].size+"</td>");
                    htmlString.push("<td>"+data.music[i].deleteurl+"</td>");
                    htmlString.push("</tr>");
                }
                $('#musicfiles').html(htmlString.join("\n"));
                $('#searchDownloadedMusic').keyup();
            }
        }, "json"
    );
} 
var refreshInterval;
$(document).ready(
    function() {
        if ($("#dlprogress").length) {
            loadList();
            refreshInterval = setInterval(loadList, 3000);
            $("#url").focus();
        }
    }
);


function checkControls()
{
    var isChecked = $('#audio_convert').prop("checked");
    $('#video_group').hide();
    $('#audio_group').hide();
    if (isChecked) {
        $('#audio_group').show();
    } else {
        $('#video_group').show();
    }
}

function helpPanel()
{
    var panelBody = $('#helppanel');
    if(!panelBody .hasClass('panel-collapsed')) {
        panelBody.slideUp();
        panelBody.addClass('panel-collapsed');
        $('#helplink').html('Click here for Info, Help and to add a Download Bookmarklet to your Browser');
    } else {
        panelBody.slideDown();
        panelBody.removeClass('panel-collapsed');
        $('#helplink').html('Click here to hide the Info and Help Panel');
    }
}

function updateBookmarklet(textchanged)
{
    var audio_format = $('#bml_audio_format').val();
    var video_format = $('#bml_format').val();
    var audio_extract = $('#bml_audio_convert').is(":checked");
    var auto_submit = $('#bml_auto_submit').is(":checked");
    var base_url = $('#bml_base_uri').val();
    var getparm = "?";
    var bml_text = $('#bml_custom_text').val();

    if (textchanged) {
        $('#bml_use_custom_text').attr("checked", true);
    }

    var use_custom = $('#bml_use_custom_text').is(":checked");

    if (audio_extract) {
        getparm += "audio=true&";
        getparm += "audio_format="+audio_format;
        $('#bml_audio_group').show();
        $('#bml_video_group').hide();
        if (!use_custom) {
            bml_text = "Download Audio";
        }
    } else {
        getparm += "format="+video_format;
        $('#bml_audio_group').hide();
        $('#bml_video_group').show();
        if (!use_custom) {
            bml_text = "Download Video";
        }
    }
    if (auto_submit) {
        getparm += "&auto_submit=true";
        if (!use_custom) {
            bml_text += " (Autostart)";
        }
    }
    getparm += "&url=";
    $('#cust_bml').html(bml_text);
    if (!use_custom) {
        $('#bml_custom_text').val(bml_text);
    }

    $('#cust_bml').attr('href', "javascript:(function(){f='"+base_url+getparm+"'+encodeURIComponent(window.location.href);a=function(){if(!window.open(f))location.href=f};if(/Firefox/.test(navigator.userAgent)){setTimeout(a,0)}else{a()}})()");
}
