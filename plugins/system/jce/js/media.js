/* jce - 2.9.33 | 2023-01-18 | https://www.joomlacontenteditor.net | Copyright (C) 2006 - 2022 Ryan Demmer. All rights reserved | GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html */
!function($){function uid(){var i,guid=(new Date).getTime().toString(32);for(i=0;i<5;i++)guid+=Math.floor(65535*Math.random()).toString(32);return"wf_"+guid+(counter++).toString(32)}function parseUrl(url){var data={};return url?(url=url.substring(url.indexOf("?")+1),$.each(url.replace(/\+/g," ").split("&"),function(i,value){var val,param=value.split("="),key=decodeURIComponent(param[0]);2===param.length&&(val=decodeURIComponent(param[1]),"string"==typeof val&&val.length&&(data[key]=val))}),data):data}function upload(url,file){return new Promise(function(resolve,reject){var xhr=new XMLHttpRequest,formData=new FormData;xhr.upload&&(xhr.upload.onprogress=function(e){e.lengthComputable&&(file.loaded=Math.min(file.size,e.loaded))}),xhr.onreadystatechange=function(){4==xhr.readyState&&(200===xhr.status?resolve(xhr.responseText):reject(),file=formData=null)};var name=file.target_name||file.name;name=name.replace(/[\+\\\/\?\#%&<>"\'=\[\]\{\},;@\^\(\)\xa3\u20ac$~]/g,"");var args={method:"upload",id:uid(),inline:1,name:name},Joomla=window.Joomla||{};if(Joomla.getOptions){var token=Joomla.getOptions("csrf.token")||"";token&&(args[token]=1)}xhr.open("post",url,!0),xhr.setRequestHeader("X-Requested-With","XMLHttpRequest"),$.each(args,function(key,value){formData.append(key,value)}),formData.append("file",file),xhr.send(formData)})}function checkMimeType(file,filter){filter=filter.replace(/[^\w_,]/gi,"").toLowerCase();var map={images:"jpg,jpeg,png,apng,gif,webp",media:"avi,wmv,wm,asf,asx,wmx,wvx,mov,qt,mpg,mpeg,m4a,m4v,swf,dcr,rm,ra,ram,divx,mp4,ogv,ogg,webm,flv,f4v,mp3,ogg,wav,xap",html:"html,htm,txt",files:"doc,docx,dot,dotx,ppt,pps,pptx,ppsx,xls,xlsx,gif,jpeg,jpg,png,webp,apng,pdf,zip,tar,gz,swf,rar,mov,mp4,m4a,flv,mkv,webm,ogg,ogv,qt,wmv,asx,asf,avi,wav,mp3,aiff,oga,odt,odg,odp,ods,odf,rtf,txt,csv,htm,html"},mimes=map[filter]||filter;return new RegExp(".("+mimes.split(",").join("|")+")$","i").test(file.name)}function getModalURL(elm){var url="",$wrapper=$(elm).parents(".field-media-wrapper"),inst=$wrapper.data("fieldMedia")||$wrapper.get(0);return inst&&(url=inst.options?inst.options.url||"":inst.getAttribute("data-url")||inst.getAttribute("url")||""),url||$(elm).siblings("a.modal").attr("href")||""}function isAdmin(value){return value&&value.indexOf("/administrator/")!=-1}function getBasePath(elm){var path="",$wrapper=$(elm).parents(".field-media-wrapper"),inst=$wrapper.data("fieldMedia")||$wrapper.get(0);return inst&&(path=inst.options?inst.options.basepath||"":inst.basePath||""),path=path||$(elm).data("basepath")||"",path&&!isAdmin(path)&&isAdmin(document.location.href)&&(path+="administrator/"),path}function createElementMedia(elm,options){if(0!=$(elm).is("joomla-field-media, .wf-media-wrapper-custom")&&0!=$(elm).hasClass("wf-media-wrapper-custom")){var modalElement=$(".joomla-modal",elm).get(0);modalElement&&window.bootstrap&&window.bootstrap.Modal&&(Joomla.initialiseModal(modalElement,{isJoomla:!0}),$(".button-select",elm).on("click",function(e){e.preventDefault(),modalElement.open()})),$(".button-clear",elm).on("click",function(e){e.preventDefault(),$(".wf-media-input",elm).val("").trigger("change")}),$(".wf-media-input",elm).on("change",function(){var path=Joomla.getOptions("system.paths",{}).root||"",src="";isImage(this.value)&&(src=path+"/"+this.value),$(".field-media-preview img",elm).attr("src",src)}).trigger("change")}}function updateMediaUrl(row,options,repeatable){$(row).find(".field-media-wrapper").add(row).each(function(){if($(this).find(".wf-media-input-upload").length&&!repeatable)return!0;var $inp=$(this).find(".field-media-input"),id=$inp.attr("id");if(!id)return!0;id=id.replace("rowX","row"+$(row).index()),createElementMedia(this,options),$(this).addClass("wf-media-wrapper");var dataUrl=$(this).data("url")||$(this).attr("url")||"",$linkBtn=$(this).find('a[href*="index.php?option=com_media"].modal.btn');$linkBtn.length&&!dataUrl&&(dataUrl=$linkBtn.attr("href")||"");var params=parseUrl(dataUrl),mediatype="images",plugin=params.plugin?params.plugin:"";params.mediatype?mediatype=params.mediatype:"files"==params.view&&(mediatype="files");var url=getBasePath($inp)+"index.php?option=com_jce&task=mediafield.display&plugin="+plugin+"&fieldid="+id+"&mediatype="+mediatype;if(options.context&&(url+="&context="+options.context),$(this).data("url")&&$(this).data("url",url),$(this).is("joomla-field-media, .wf-media-wrapper-custom")){$(this).attr("url",url);var ifrHtml=Joomla.sanitizeHtml('<iframe src="'+url+'" class="iframe" title="" width="100%" height="100%"></iframe>',{iframe:["src","class","title","width","height"]});$(this).find(".joomla-modal").attr("data-url",url).attr("data-iframe",ifrHtml)}$linkBtn.length&&$linkBtn.attr("href",url)})}function cleanInputValue(elm){var val=$(elm).val()||"";val.indexOf("#joomlaImage")!=-1&&(val=val.substring(0,val.indexOf("#")),$(elm).val(val).attr("value",val))}function isImage(value){return value&&/\.(jpg|jpeg|png|gif|svg|apng|webp)$/.test(value)}var counter=0;$.fn.WfMediaUpload=function(){return this.each(function(){function insertFile(value){var $wrapper=$(elm).parents(".field-media-wrapper"),inst=$wrapper.data("fieldMedia")||$wrapper.get(0);return inst&&inst.setValue?inst.setValue(value):$(elm).val(value).trigger("change"),!0}function uploadAndInsert(url,file){if(!file.name)return!1;var params=parseUrl(url),url=getBasePath(elm)+"index.php?option=com_jce",validParams=["task","context","plugin","filter","mediatype"],filter=params.filter||params.mediatype||"images";return checkMimeType(file,filter)?(params.task="plugin.rpc",$.each(params,function(key,value){$.inArray(key,validParams)===-1&&delete params[key]}),url+="&"+$.param(params),$(elm).prop("disabled",!0).addClass("wf-media-upload-busy"),void upload(url,file).then(function(response){$(elm).prop("disabled",!1).removeAttr("disabled").removeClass("wf-media-upload-busy");try{var o=JSON.parse(response),error="Unable to upload file";if($.isPlainObject(o)){o.error&&(error=o.error.message||error);var r=o.result;if(r){var files=r.files||[],item=files.length?files[0]:{};if(item.file)return insertFile(item.file)}}alert(error)}catch(e){alert("The server returned an invalid JSON response")}},function(){return $(elm).prop("disabled",!1).removeAttr("disabled").removeClass("wf-media-upload-busy"),!1})):(alert("The selected file is not supported."),!1)}var elm=this,url=getModalURL(elm);if(!url)return!1;var $uploadBtn=$('<a title="Upload" role="button" class="btn btn-outline-secondary wf-media-upload-button" aria-label="Upload"><i role="presentation" class="icon-upload"></i><input type="file" aria-hidden="true" /></a>');$('input[type="file"]',$uploadBtn).on("change",function(e){if(e.preventDefault(),this.files){var file=this.files[0];file&&uploadAndInsert(url,file)}});var $selectBtn=$(elm).parent().find(".button-select, .modal.btn");$uploadBtn.insertAfter($selectBtn),$(elm).on("drag dragstart dragend dragover dragenter dragleave drop",function(e){e.preventDefault(),e.stopPropagation()}).on("dragover dragenter",function(e){$(this).addClass("wf-media-upload-hover")}).on("dragleave",function(e){$(this).removeClass("wf-media-upload-hover")}).on("drop",function(e){var dataTransfer=e.originalEvent.dataTransfer;if(dataTransfer&&dataTransfer.files&&dataTransfer.files.length){var file=dataTransfer.files[0];file&&uploadAndInsert(url,file)}$(this).removeClass("wf-media-upload-hover")})})},$(document).ready(function($){function canProcessField(elm){return options.replace_media||$(elm).find(".wf-media-input").length}var options=Joomla.getOptions("plg_system_jce",{});options.replace_media&&$(".field-media-wrapper, .fc-field-value-properties-box").find(".field-media-input").addClass("wf-media-input"),$(".wf-media-input").removeAttr("readonly").parents(".field-media-wrapper").addClass("wf-media-wrapper"),$(".wf-media-input").parents(".subform-repeatable-group").each(function(i,row){updateMediaUrl(row,options,!0)}),$("joomla-field-media.wf-media-wrapper").each(function(){var field=this;if(field.inputElement){cleanInputValue(field.inputElement);var markValidFunction=field.markValid||function(){};field.markValid=function(){cleanInputValue(this.inputElement),field.querySelector('label[for="'+this.inputElement.id+'"]')&&markValidFunction.apply(this)},field.inputElement.addEventListener("change",function(e){e.stopImmediatePropagation(),field.querySelector('label[for="'+this.id+'"]')&&markValidFunction.apply(this),field.updatePreview(),$(document).trigger("t4:media-selected",{selectedUrl:field.basePath+this.value})},!0)}updateMediaUrl(this,options)}),$(".wf-media-wrapper-custom").each(function(){updateMediaUrl(this,options,!0)}),$(document).on("subform-row-add",function(evt,row){var originalEvent=evt.originalEvent;originalEvent&&originalEvent.detail&&(row=originalEvent.detail.row||row),canProcessField(row)&&($(row).find(".wf-media-input, .field-media-input").removeAttr("readonly").addClass("wf-media-input wf-media-input-active"),updateMediaUrl(row,options,!0))}),$(".wf-media-input-upload").not('[name*="media-repeat"]').WfMediaUpload(),$(".wf-media-wrapper .modal-header h3").html("&nbsp;")})}(jQuery);