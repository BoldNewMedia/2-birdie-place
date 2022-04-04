/* jce - 2.9.22 | 2022-03-31 | https://www.joomlacontenteditor.net | Copyright (C) 2006 - 2022 Ryan Demmer. All rights reserved | GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html */
!function($){function getAlignment(style){return style.float?"right"==style.float?"right":"left":style["vertical-align"]?style["vertical-align"]:style.display&&"block"==style.display&&style["margin-left"]&&"auto"==style["margin-left"]?"center":""}function hasMediaPreview(type){return"iframe"===type||"video"===type||"audio"===type}function getTypeFromMime(mimetype){var map={"application/x-shockwave-flash":"flash","application/x-director":"shockwave","video/quicktime":"quicktime","application/x-mplayer2":"windowsmedia","audio/x-pn-realaudio-plugin":"real","video/divx":"divx","video/mp4":"video","video/ogg":"video","video/webm":"video","audio/mpeg":"audio","audio/mp3":"audio","audio/x-wav":"audio","audio/ogg":"audio","audio/webm":"audio","application/x-silverlight-2":"silverlight","video/x-flv":"video"};return map[mimetype]||""}function getMimeFromType(type){var map={flash:"application/x-shockwave-flash",director:"application/x-director",shockwave:"application/x-director",quicktime:"video/quicktime",mplayer:"application/x-mplayer2",windowsmedia:"application/x-mplayer2",realaudio:"audio/x-pn-realaudio-plugin",real:"audio/x-pn-realaudio-plugin",divx:"video/divx",flv:"video/x-flv",silverlight:"application/x-silverlight-2",audio:"audio/mpeg",video:"video/mpeg"};return map[type]||""}function removeQuery(s){return s?(s.indexOf("?")!==-1?s=s.substr(0,s.indexOf("?")):s.indexOf("&")!==-1&&(s=s.replace(/&amp;/g,"&"),s=s.substr(0,s.indexOf("&"))),s):s}function getMimeFromUrl(url){url=removeQuery(url);var ext=Wf.String.getExt(url);return ext=ext.toLowerCase(),mimes[ext]||!1}function getMediaType(val){var type,cls=/mce-object-(flash|shockwave|windowsmedia|quicktime|realmedia|divx|silverlight|audio|video|iframe)/.exec(val);return cls&&(type=cls[1].toLowerCase()),type||"video"}var each=tinymce.each,htmlSchema=new tinymce.html.Schema({schema:"mixed"}),mediatypes=["flash","shockwave","windowsmedia","quicktime","realmedia","divx","silverlight","audio","video","iframe"],defaultMediaAttributes={quicktime:{autoplay:!0,controller:!0,loop:!1,cache:!1,correction:!1,enablejavascript:!1,kioskmode:!1,autohref:!1,playeveryframe:!1,targetcache:!1},flash:{play:!0,loop:!0,menu:!0,swliveconnect:!1,allowfullscreen:!1},director:{swstretchstyle:"none",swstretchhalign:"none",swstretchvalign:"none",autostart:!1,sound:!0,swliveconnect:!1,progress:!0},windowsmedia:{autostart:!0,enablecontextmenu:!0,invokeurls:!0,enabled:!1,fullscreen:!1,mute:!1,stretchtofit:!1,windowlessvideo:!1},real:{autogotourl:!0,imagestatus:!0},video:{autoplay:!1,loop:!1,controls:!1,muted:!1,playsinline:!1},audio:{autoplay:!1,loop:!1,controls:!1,muted:!1,preload:!1}},mimes={};!function(data){var i,y,ext,items=data.split(/,/);for(i=0;i<items.length;i+=2)for(ext=items[i+1].split(/ /),y=0;y<ext.length;y++)mimes[ext[y]]=items[i]}("application/x-mplayer2,avi wmv wm asf asx wmx wvx,application/x-director,dcrvideo/divx,divxapplication/pdf,pdf,application/x-shockwave-flash,swf swfl,audio/mpeg,mpga mpega mp2 mp3 m4a,audio/ogg,ogg spx oga,audio/x-wav,wav,video/mpeg,mpeg mpg mpe,video/mp4,mp4 m4v,video/ogg,ogg ogv,video/webm,webm,video/quicktime,qt mov,video/x-flv,flv f4v,video/vnd.rn-realvideo,rvvideo/3gpp,3gpvideo/x-matroska,mkv");var MediaManagerDialog={settings:{filebrowser:{}},mediatypes:null,convertURL:function(url){var ed=tinyMCEPopup.editor;if(!url)return url;var query="",n=url.indexOf("?");return n===-1&&(url=url.replace(/&amp;/g,"&"),n=url.indexOf("&")),n>0&&(query=url.substring(n+1,url.length),url=url.substr(0,n)),url=ed.convertURL(url),url+(query?"?"+query:"")},init:function(){tinyMCEPopup.restoreSelection();var mt,attribs,self=this,ed=tinyMCEPopup.editor,elm=ed.selection.getNode(),mediatype="video";if($("button#insert").on("click",function(e){self.insert(),e.preventDefault()}),this.mediatypes=this.mapTypes(),Wf.init(),WFPopups.setup({remove:function(e,el){ed.dom.remove(ed.dom.getParent(el,"a"),1)}}),WFAggregator.setup(),/mce-object/.test(elm.className)){var mediaApi=ed.plugins.media,data=mediaApi.getMediaData(),attribs={};mediatype=getMediaType(elm.className),each(data,function(value,name){if("html"===name)return attribs[name]=value,!0;if(htmlSchema.isValid("img",name)||(name=mediatype+"_"+name),"class"===name&&(name="classes",value=value.replace(/mce-(\S+)/g,"").replace(/\s+/g," ").trim()),"style"===name){var styleObject=ed.dom.parseStyle(value);attribs.align=getAlignment(styleObject),each(["top","right","bottom","left"],function(pos){attribs["margin_"+pos]=self.getAttrib(elm,"margin-"+pos),delete styleObject["margin-"+pos]}),each(["width","style","color"],function(at){attribs["border_"+at]=self.getAttrib(elm,"border-"+at),delete styleObject["border-"+at]}),each(["width","height"],function(at){var val=styleObject[at];val&&!data[at]&&(attribs[at]=val.replace(/px/,"")),delete styleObject[at]}),value=ed.dom.serializeStyle(styleObject)}"align"===name&&(value=self.getAttrib(elm,"align")),attribs[name]=value}),$("#popup_list").prop("disabled",!0)}else WFPopups.getPopup(elm,0,function(popup){return attribs={},popup.type||(popup.type=getMimeFromUrl(popup.src)),mediatype=getTypeFromMime(popup.type),each(popup,function(value,name){var key=name;return"src"!==name&&"source"!==name||(value=self.convertURL(value)),"source"===name&&(value=[value]),htmlSchema.isValid("img",name)||(name=mediatype+"_"+key),delete popup[key],"type"===key||void(attribs[name]=value)}),popup});if(attribs){$("#insert").button("option","label",tinyMCEPopup.getLang("update","Update",!0)),each(["width","height"],function(key){var value=attribs[key];$("#"+key).val(value).data("tmp",value)}),(mt=WFAggregator.isSupported(attribs))&&(attribs=WFAggregator.setValues(mt,attribs),mediatype=mt);var x=0;each(attribs,function(value,key){if("width"===key||"height"===key)return!0;if(Array.isArray(value))return each(value,function(val,i){$('input[name="'+key+'[]"]').eq(i).val(val).trigger("change")}),!0;var $na=$("#"+key);if($na.length)$na.is(":checkbox")?("false"!=value&&"0"!=value||(value=!1),$na.prop("checked",!!value).trigger("change")):$na.val(value);else{if(key.substr(0,mediatype.length)!==mediatype){var $repeatable=$(".uk-repeatable","#advanced_tab");x>0&&$repeatable.eq(0).clone(!0).appendTo($repeatable.parent());var $elements=$repeatable.eq(x).find("input, select");return key=key.replace(new RegExp("^("+mediatypes.join("|")+")_"),""),$elements.eq(0).val(key),$elements.eq(1).val(value),void x++}key=key.substr(mediatype.length+1);var $repeatable=$(".uk-repeatable",".media_option."+mediatype);x>0&&$repeatable.eq(0).clone(!0).appendTo($repeatable.parent());var $elements=$repeatable.eq(x).find("input, select");$elements.eq(0).val(key),$elements.eq(1).val(value),x++}}),"audio"!=mediatype&&"video"!=mediatype||$(":input, select","#"+mediatype+"_options").each(function(){$(this).is(":checkbox")?$(this).prop("checked",!1):$(this).val("")})}else Wf.setDefaults(this.settings.defaults);$("#media_type").val(mediatype).trigger("change"),Wf.updateStyles(),attribs=attribs||{width:"",height:""},$("#src").filebrowser().on("filebrowser:onfileclick",function(e,file,data){self.selectFile(file,data)}).on("filebrowser:onfiledetails",function(e,item,data){data.width&&!attribs.width&&$("#width").val(data.width).data("tmp",data.width).trigger("change"),data.height&&!attribs.height&&$("#height").val(data.height).data("tmp",data.height).trigger("change"),attribs.width=attribs.height=null}),$("#src").on("change",function(){this.value&&self.selectType(this.value)}),$("#width, #height").on("change",function(){var n=$(this).attr("id"),v=this.value;"audio"===$("#media_type").val()&&self.addStyle(n,v)}),$("#border").change(),$(".uk-equalize-checkbox").trigger("equalize:update"),$(".uk-form-controls select:not(.uk-datalist)").datalist({input:!1}).trigger("datalist:update"),$(".uk-datalist").trigger("datalist:update"),$(".uk-repeatable").on("repeatable:delete",function(e,ctrl,elm){$(elm).find("input, select").eq(1).val("")})},getAttrib:function(node,attrib){return Wf.getAttrib(node,attrib)},getSiteRoot:function(){var s=tinyMCEPopup.getParam("document_base_url");return s.match(/.*:\/\/([^\/]+)(.*)/)[2]},setControllerHeight:function(t){var v=0;switch(t){case"quicktime":v=16;break;case"windowsmedia":v=16;break;case"divx":switch($("#divx_mode").val()){default:v=0;break;case"mini":v=20;break;case"large":v=65;break;case"full":v=90}}$("#controller_height").val(v)},isIframe:function(n){return n&&n.className.indexOf("mce-object-iframe")!==-1},addStyle:function(style,value){var styles=$("<div />").attr("style",$("#style").val()).css(style,value).get(0).style.cssText;$("#style").val(styles)},insert:function(){var src=$("#src").val(),type=$("#media_type").val();return""==src?(Wf.Modal.alert(tinyMCEPopup.getLang("mediamanager_dlg.no_src","Please select a file or enter in a link to a file")),!1):$("#width").val()&&$("#height").val()?/(windowsmedia|mplayer|quicktime|divx)$/.test(type)?(Wf.Modal.confirm(tinyMCEPopup.getLang("mediamanager_dlg.add_controls_height","Add additional height for player controls?"),function(state){if(state){var h=$("#height").val(),ch=$("#controller_height").val();ch&&$("#height").val(parseInt(h,10)+parseInt(ch,10))}MediaManagerDialog.insertAndClose()}),!1):void this.insertAndClose():("audio"===type&&this.insertAndClose(),WFPopups.isEnabled()&&this.insertAndClose(),Wf.Modal.alert(tinyMCEPopup.getLang("mediamanager_dlg.no_dimensions","A width and height value are required."),{close:function(){$("#width, #height").map(function(){if(!this.value)return this}).first().focus()}}),!1)},insertAndClose:function(){tinyMCEPopup.restoreSelection();var provider,ed=tinyMCEPopup.editor,classes=["mce-object"],attribs={},args={},data={},popupData={},mediatype=$("#media_type").val(),elm=ed.selection.getNode();mediatype==WFAggregator.isSupported($("#src").val())&&(WFAggregator.onInsert(mediatype),mediatype=WFAggregator.getType(mediatype));var node=hasMediaPreview(mediatype)?mediatype:"object";if(classes.push("mce-object-"+mediatype),$("input[id], select[id]").each(function(){var val=$(this).val();$(this).is(":checkbox")&&(val=!!$(this).is(":checked")),data[this.id]=val}),(provider=WFAggregator.isSupported(data.src))&&$.extend(!0,data,WFAggregator.getValues(provider,data.src)),"audio"===mediatype||"video"===mediatype){var sources=[];$('input[name="'+mediatype+'_source[]"]').each(function(){var val=$(this).val();val!==data.src&&sources.push(val)}),sources.length&&(data[mediatype+"_source"]=sources)}if("audio"===mediatype){delete data.width,delete data.height;var agent=navigator.userAgent.match(/(Opera|Chrome|Safari|Gecko)/);agent&&classes.push("mce-object-agent-"+agent[0].toLowerCase())}each(data,function(value,name){return"classes"===name?(attribs.class=value,!0):!htmlSchema.isValid(node,name)||(""===value||void(attribs[name]=value))}),"audio"!==mediatype&&(attribs["data-mce-width"]=attribs.width||384,attribs["data-mce-height"]=attribs.height||216);var innerHTML="";attribs.class=$.trim(attribs.class+" "+classes.join(" ")),"object"===node&&(attribs.data=data.src,attribs.type=getMimeFromType(mediatype),"windowsmedia"===mediatype&&(data.windowsmedia_url=data.src),"quicktime"===mediatype&&(data.quicktime_src=data.src),"flash"===mediatype&&(data.flash_movie=data.src)),$(".uk-repeatable",".media_option."+mediatype).each(function(){var elements=$("input, select",this),key=$(elements).eq(0).val(),value=$(elements).eq(1).val();key&&(data[mediatype+"_"+key]=value)}),$(".uk-repeatable","#advanced_tab").each(function(){var elements=$("input, select",this),key=$(elements).eq(0).val(),value=$(elements).eq(1).val();key&&(attribs[key]=value)}),each(data,function(value,name){return 0!==name.indexOf(mediatype)||(name=name.replace(mediatype+"_",""),!(!defaultMediaAttributes[mediatype]||value!==defaultMediaAttributes[mediatype][name])||(""===value||("source"===name?(each(value,function(source){if(!source)return!0;var mimetype=getMimeFromUrl(source);mimetype=mimetype||mediatype+"/mpeg",mimetype=mimetype.replace(/(audio|video)/,mediatype),innerHTML+='<source src="'+source+'" type="'+mimetype+'" />',popupData.source=source}),!0):(popupData[name]=value,"object"===node?(innerHTML+='<param name="'+name+'" value="'+value+'" />',!0):void(attribs[name]=value)))))}),$("#html").val()&&(innerHTML+=$("#html").val());var mediaApi=ed.plugins.media;if(elm&&mediaApi.isMediaObject(elm))attribs.html=innerHTML,mediaApi.updateMedia(attribs);else if(WFPopups.isEnabled()&&($("#popup_text").is(":disabled")||""!=$("#popup_text").val())){var args={type:getMimeFromUrl(attribs.src),data:popupData};each(attribs,function(value,name){return 0===name.indexOf("data-mce-")||void(args[name]=value)}),WFPopups.createPopup(elm,args)}else{var html=ed.dom.createHTML(node,attribs,$.trim(innerHTML));ed.execCommand("mceInsertContent",!1,html,{skip_undo:1})}ed.undoManager.add(),ed.nodeChanged(),tinyMCEPopup.close()},mapTypes:function(){var types={},mt=this.settings.media_types;return tinymce.each(tinymce.explode(mt,";"),function(v,k){v&&(v=v.replace(/([a-z0-9]+)=([a-z0-9,]+)/,function(a,b,c){types[b]=c.split(",")}))}),types},checkType:function(src){var mime=getMimeFromUrl(src);return!!mime&&(getTypeFromMime(mime)||!1)},getType:function(v){var s,type,n,data={width:"",height:""};if(!v)return!1;/\.([a-z0-9]{3,4}$)/i.test(v)&&(type=this.checkType(v)),type||(s=WFAggregator.isSupported(v))&&(data=WFAggregator.getAttributes(s,v),type=s);for(n in data){var v=data[n];if(v)if("width"===n||"height"===n)$("#"+n).val(v).trigger("change");else{var $el=$("#"+n);$el.is(":checkbox")?$el.attr("checked",!!parseFloat(v)).prop("checked",!!parseFloat(v)):$el.val(v)}}return type},selectType:function(v){var type=this.getType(v);type&&$("#media_type").val(type).trigger("change")},changeType:function(type){var type=type||$("#media_type").val();this.setControllerHeight(type),$(".media_option","#media_tab").hide().filter("."+type).show()},checkPrefix:function(n){/^\s*www./i.test(n.value)&&confirm(tinyMCEPopup.getLang("mediamanager_dlg_is_external",!1,"The URL you entered seems to be an external link, do you want to add the required http:// prefix?"))&&(n.value="http://"+n.value)},setSourceFocus:function(n){$("input.uk-active").removeClass("uk-active"),$(n).addClass("uk-active")},selectFile:function(file,data){var name=data.title,src=data.url;$("#media_tab").hasClass("uk-active")?$("input.uk-active","#media_tab").val(src):($("#src").val(src),MediaManagerDialog.selectType(name),data.width&&data.height&&($("#width").val(data.width).data("tmp",data.width),$("#height").val(data.height).data("tmp",data.height)),WFAggregator.isSupported(src)&&WFAggregator.onSelectFile(name))}};window.MediaManagerDialog=MediaManagerDialog,tinyMCEPopup.onInit.add(MediaManagerDialog.init,MediaManagerDialog)}(jQuery,tinyMCEPopup);