/* jce - 2.9.22 | 2022-03-31 | https://www.joomlacontenteditor.net | Copyright (C) 2006 - 2022 Ryan Demmer. All rights reserved | GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html */
!function(){function dataToHtml(data){var frag=new DomParser({validate:!1,root_name:"#document"}).parse(data),body=frag.getAll("body")[0]||frag,html=new Serializer({validate:!1}).serialize(body);return html}var each=tinymce.each,DomParser=tinymce.html.DomParser,Serializer=tinymce.html.Serializer,XHR=tinymce.util.XHR,fontIconRe=/<([a-z0-9]+)([^>]+)class="([^"]*)(glyph|uk-)?(fa|icon)-([\w-]+)([^"]*)"([^>]*)>(&nbsp;|\u00a0)?<\/\1>/gi,templateData={};tinymce.create("tinymce.plugins.TemplatePlugin",{init:function(ed,url){function isEmpty(){var content=ed.getContent();return""==content||"<p>&nbsp;</p>"==content}var self=this;self.editor=ed,self.contentLoaded=!1,this.params=ed.getParam("templatemanager",{}),ed.addCommand("mceTemplate",function(ui){ed.windowManager.open({file:ed.getParam("site_url")+"index.php?option=com_jce&task=plugin.display&plugin=templatemanager",size:"mce-modal-landscape-xxlarge"},{plugin_url:url})}),ed.onInit.add(function(){ed&&ed.plugins.contextmenu&&ed.plugins.contextmenu.onContextMenu.add(function(th,m,e){m.add({title:"templatemanager.desc",icon:"templatemanager",cmd:"mceTemplate"})})}),ed.addCommand("mceInsertTemplate",function(ui,o){var html=self._processContent(o.content);return self._insertTemplate(html)}),templateData=this.params.templates||"",templateData||ed.addButton("templatemanager",{title:"templatemanager.desc",cmd:"mceTemplate"}),ed.onPreProcess.add(function(ed,o){var dom=ed.dom,mdate_classes=self.params.mdate_classes||"mdate modifieddate",mdate_format=self.params.mdate_format||ed.getLang("templatemanager.mdate_format");each(dom.select("div",o.node),function(e){dom.hasClass(e,"mceTmpl")&&(each(dom.select("*",e),function(e){dom.hasClass(e,mdate_classes.replace(/\s+/g,"|"))&&(e.innerHTML=self._getDateTime(new Date,mdate_format))}),self._replaceVals(e))})});var content_url=self.params.content_url||"";content_url&&ed.onInit.add(function(){!self.contentLoaded&&isEmpty()&&(/http(s)?:\/\//.test(content_url)||(ed.setProgressState(!0),XHR.send({url:ed.settings.document_base_url+"/"+content_url,success:function(value){var html=dataToHtml(value);html&&ed.execCommand("mceInsertTemplate",!1,{content:html}),ed.setProgressState(!1),self.contentLoaded=!0},error:function(e){ed.setProgressState(!1),self.contentLoaded=!0}})))})},_processContent:function(html){function hasClass(n,c){var cls=ed.dom.getAttrib(n,"class","");return new RegExp("\\b"+c+"\\b","g").test(cls)}var el,self=this,ed=self.editor,dom=ed.dom,replace_values=self.params.replace_values||{};each(replace_values,function(v,k){"function"!=typeof v&&(html=html.replace(new RegExp("\\{\\$"+k+"\\}","g"),v))}),el=dom.create("div",null,html);var cdate_classes=this.params.cdate_classes||"cdate creationdate",cdate_format=this.params.cdate_format||ed.getLang("templatemanager.cdate_format"),mdate_classes=this.params.mdate_classes||"mdate modifieddate",mdate_format=this.params.mdate_format||ed.getLang("templatemanager.mdate_format"),selected_content_classes=this.params.selected_content_classes||"selcontent",selection=ed.selection.getContent();return each(dom.select("*",el),function(n){cdate_classes&&hasClass(n,cdate_classes.replace(/\s+/g,"|"))&&(n.innerHTML=self._getDateTime(new Date,cdate_format)),mdate_classes&&hasClass(n,mdate_classes.replace(/\s+/g,"|"))&&(n.innerHTML=self._getDateTime(new Date,mdate_format)),selected_content_classes&&hasClass(n,selected_content_classes.replace(/\s+/g,"|"))&&(n.innerHTML=selection)}),self._replaceVals(el),el.innerHTML},_insertTemplate:function(html){var self=this,ed=self.editor;ed.settings.validate===!1&&(ed.settings.validate=!0),html=html.replace(fontIconRe,'<$1$2class="$3$4$5-$6$7"$8>&nbsp;</$1>'),html=html.replace(/<(a|i|span)([^>]+)><\/\1>/gi,"<$1$2>&nbsp;</$1>"),ed.execCommand("mceInsertContent",!1,html),ed.settings.verify_html===!1&&(ed.settings.validate=!1),ed.addVisual()},_replaceVals:function(e){var dom=this.editor.dom,vl=this.params.replace_values||{};each(dom.select("*",e),function(e){each(vl,function(v,k){dom.hasClass(e,k)&&"function"==typeof vl[k]&&vl[k](e)})})},_getDateTime:function(d,fmt){function addZeros(value,len){var i;if(value=""+value,value.length<len)for(i=0;i<len-value.length;i++)value="0"+value;return value}var ed=this.editor;return fmt?(fmt=fmt.replace("%D","%m/%d/%y"),fmt=fmt.replace("%r","%I:%M:%S %p"),fmt=fmt.replace("%Y",""+d.getFullYear()),fmt=fmt.replace("%y",""+d.getYear()),fmt=fmt.replace("%m",addZeros(d.getMonth()+1,2)),fmt=fmt.replace("%d",addZeros(d.getDate(),2)),fmt=fmt.replace("%H",""+addZeros(d.getHours(),2)),fmt=fmt.replace("%M",""+addZeros(d.getMinutes(),2)),fmt=fmt.replace("%S",""+addZeros(d.getSeconds(),2)),fmt=fmt.replace("%I",""+((d.getHours()+11)%12+1)),fmt=fmt.replace("%p",""+(d.getHours()<12?"AM":"PM")),fmt=fmt.replace("%B",""+ed.getLang("templatemanager_months_long").split(",")[d.getMonth()]),fmt=fmt.replace("%b",""+ed.getLang("templatemanager_months_short").split(",")[d.getMonth()]),fmt=fmt.replace("%A",""+ed.getLang("templatemanager_day_long").split(",")[d.getDay()]),fmt=fmt.replace("%a",""+ed.getLang("templatemanager_day_short").split(",")[d.getDay()]),fmt=fmt.replace("%%","%")):""},createControl:function(name,cm){var btn,self=this,ed=self.editor;if("templatemanager"==name&&templateData){var onSelectTemplate=function(value){/\.(html|html|txt|md)$/i.test(value)?(ed.setProgressState(!0),XHR.send({url:value,success:function(val){var html=dataToHtml(val);html&&(ed.execCommand("mceInsertTemplate",!1,{content:html}),ed.setProgressState(!1))}})):ed.execCommand("mceInsertTemplate",!1,{content:value})},btn=cm.createSplitButton("templatemanager",{title:"templatemanager.desc",cmd:"mceTemplate",class:"mce_templatemanager"});return btn.onRenderMenu.add(function(btn,menu){each(templateData,function(value,name){"string"==typeof value&&(value={data:value,image:""});var item=menu.add({id:ed.dom.uniqueId(),title:name,image:value.image,onclick:function(e){return item.setSelected(!1),onSelectTemplate(value.data),!1}})})}),btn}},insertUploadedFile:function(o){var ed=this.editor,data=this.getUploadConfig();if(data&&data.filetypes&&new RegExp(".("+data.filetypes.join("|")+")$","i").test(o.name)){if(o.data){var html=dataToHtml(o.data);html&&ed.execCommand("mceInsertTemplate",!1,{content:html})}return!0}return!1},getUploadURL:function(file){var ed=this.editor,data=this.getUploadConfig();return!!(data&&data.filetypes&&new RegExp(".("+data.filetypes.join("|")+")$","i").test(file.name))&&ed.getParam("site_url")+"index.php?option=com_jce&task=plugin.display&plugin=templatemanager"},getUploadConfig:function(){return this.params.upload||{}}}),tinymce.PluginManager.add("templatemanager",tinymce.plugins.TemplatePlugin)}();