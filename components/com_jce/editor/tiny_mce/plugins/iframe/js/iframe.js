/* jce - 2.9.10 | 2021-07-08 | https://www.joomlacontenteditor.net | Copyright (C) 2006 - 2021 Ryan Demmer. All rights reserved | GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html */
!function($){var each=tinymce.each,htmlSchema=new tinymce.html.Schema({schema:"html5-strict"}),defaultAttributes={frameborder:1,scrolling:"auto"},validAttributes=["scrolling","frameborder"],IframeDialog={settings:{},init:function(){var attribs,self=this,ed=tinyMCEPopup.editor,elm=ed.selection.getNode();if(tinyMCEPopup.restoreSelection(),TinyMCE_Utils.fillClassList("classlist"),Wf.init(),this.settings.file_browser&&Wf.createBrowsers($("#src"),function(files,data){file=data[0],$("#src").val(file.url),file.width&&$("#width").val(file.width).data("tmp",file.width).trigger("change"),file.height&&$("#height").val(file.height).data("tmp",file.height).trigger("change")}),$("#insert").on("click",function(){self.insert()}),WFAggregator.setup({embed:!1}),/mce-object/.test(elm.className)){var mediatype,mediaApi=ed.plugins.media,data=mediaApi.getMediaData(),attribs={};if(each(data,function(value,name){if("class"===name&&(name="classes",value=value.replace(/mce-(.*)/g,"").replace(/\s+/g," ").trim()),"style"===name){var styleObject=ed.dom.parseStyle(value);each(["top","right","bottom","left"],function(pos){attribs["margin_"+pos]=self.getAttrib(elm,"margin-"+pos),delete styleObject["margin-"+pos]}),tinymce.each(["width","style","color"],function(at){attribs["border_"+at]=self.getAttrib(elm,"border-"+at),delete styleObject["border-"+at]}),delete styleObject.width,delete styleObject.height,value=ed.dom.serializeStyle(styleObject)}"align"===name&&(value=self.getAttrib(elm,"align")),attribs[name]=value}),attribs){$("#insert").button("option","label",tinyMCEPopup.getLang("update","Update",!0)),each(["width","height"],function(key){var value=attribs[key];$("#"+key).val(value).data("tmp",value)}),(mediatype=WFAggregator.isSupported(attribs))?(attribs=WFAggregator.setValues(mediatype,attribs),$(".aggregator_option, .options_description","#options_tab").hide().filter("."+mediatype).show()):$(".options_description","#options_tab").show(),$("#src").val(attribs.src||"");var x=0;each(attribs,function(value,key){if("width"===key||"height"===key||"src"===key)return!0;if(Array.isArray(value))return each(value,function(val,i){$('input[name="'+key+'[]"]').eq(i).val(val).trigger("change")}),!0;var $na=$("#"+key);if($na.length)$na.is(":checkbox")?("false"!=value&&"0"!=value||(value=!1),$na.prop("checked",!!value).trigger("change")):$na.val(value).trigger("change");else if(mediatype){if(key.substr(0,mediatype.length)!==mediatype)return!0;key=key.substr(mediatype.length+1);var n=$(".uk-repeatable",".aggregator_option."+mediatype).eq(0);x>0&&$(n).clone(!0).appendTo($(n).parent());var elements=$(".uk-repeatable",".aggregator_option."+mediatype).eq(x).find("input, select");$(elements).eq(0).val(key),$(elements).eq(1).val(value),x++}})}}else Wf.setDefaults(this.settings.defaults);$("#src").on("change",function(){var mediatype,data={},val=this.value;(mediatype=WFAggregator.isSupported(val))?(data=WFAggregator.getAttributes(mediatype,val),$(".aggregator_option, .options_description","#options_tab").hide().filter("."+mediatype).show()):$(".options_description","#options_tab").show();for(key in data){var $el=$("#"+key),val=data[key];"width"==key||"height"==key?""!==$el.val()&&$el.hasClass("edited")!==!1||$("#"+key).val(data[key]).data("tmp",data[key]).trigger("change"):$el.is(":checkbox")?(val=parseInt(val),$el.attr("checked",val).prop("checked",val)):$el.val(val)}}),$(".uk-equalize-checkbox").trigger("equalize:update"),$(".uk-form-controls select").datalist().trigger("datalist:update"),$(".uk-datalist").trigger("datalist:update")},getAttrib:function(e,at){return Wf.getAttrib(e,at)},checkPrefix:function(n){var self=this,v=$(n).val();/^\s*www./i.test(v)?Wf.Modal.confirm(tinyMCEPopup.getLang("iframe_dlg.is_external","The URL you entered seems to be an external link, do you want to add the required http:// prefix?"),function(state){state&&$(n).val("http://"+v),self.insert()}):this.insertAndClose()},insert:function(){tinyMCEPopup.editor;return""===$("#src").val()?(Wf.Modal.alert(tinyMCEPopup.getLang("iframe_dlg.no_src","Please enter a url for the iframe")),!1):""===$("#width").val()||""===$("#height").val()?(Wf.Modal.alert(tinyMCEPopup.getLang("iframe_dlg.no_dimensions","Please enter a width and height for the iframe")),!1):this.checkPrefix($("#src"))},insertAndClose:function(){tinyMCEPopup.restoreSelection();var provider,ed=tinyMCEPopup.editor,data={},args={},elm=ed.selection.getNode();$("input[id], select[id]").each(function(){var value=$(this).val(),name=this.id;$(this).is(":checkbox")&&(value=!!$(this).is(":checked")),"frameborder"===name&&(value=value?1:0),"classes"===name&&(name="class"),value===defaultAttributes[name]&&(value=""),(tinymce.inArray(validAttributes,name)!==-1||htmlSchema.isValid("iframe",name))&&(data[name]=value)}),data.width=data.width||384,data.height=data.height||216,elm=ed.dom.getParent(elm,".mce-object-iframe"),ed.dom.hasClass(elm,"mce-object-preview")&&(elm=elm.firstChild);var innerHTML=$.trim($("#html").val());(provider=WFAggregator.isSupported(data.src))&&$.extend(!0,data,WFAggregator.getValues(provider,data.src)),ed.undoManager.add();var mediaApi=ed.plugins.media;if(elm)data.html=innerHTML,mediaApi.updateMedia(data);else{each(data,function(value,name){""!==value&&(args[name]=value)});var html=ed.dom.createHTML("iframe",args,innerHTML);ed.execCommand("mceInsertContent",!1,html,{skip_undo:1})}tinyMCEPopup.close()}};window.IframeDialog=IframeDialog,tinyMCEPopup.onInit.add(IframeDialog.init,IframeDialog)}(jQuery,tinyMCEPopup);