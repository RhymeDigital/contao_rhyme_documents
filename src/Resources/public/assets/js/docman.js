var DocMan={toggleFeatured:function(e,t){e.blur();var n=$(e).getFirst("img");return-1==n.src.indexOf("featured_")?(n.src=n.src.replace("featured.gif","featured_.gif"),(new Request.Contao).post({action:"toggleFeaturedDoc",id:t,state:0,REQUEST_TOKEN:Contao.request_token})):(n.src=n.src.replace("featured_.gif","featured.gif"),(new Request.Contao).post({action:"toggleFeaturedDoc",id:t,state:1,REQUEST_TOKEN:Contao.request_token})),!1},documentWizard:function(e,t,n){var r,a,c,o,i,s,l=$(n).getElement("tbody"),g=$(e).getParent("tr"),d=l.getChildren(),u=l.get("data-tabindex");switch(Backend.getScrollOffset(),t){case"copy":var f=new Element("tr");for(c=g.getChildren(),i=0;i<c.length;i++){var h=c[i].clone(!0).inject(f,"bottom");(a=c[i].getFirst("select"))&&(h.getFirst("select").value=a.value)}f.inject(g,"after"),f.getElement(".chzn-container").destroy(),new Chosen(f.getElement("select.tl_select")),window.Stylect&&Stylect.convertSelects();break;case"up":f===g.getPrevious("tr")?g.inject(f,"before"):g.inject(l,"bottom");break;case"down":f===g.getNext("tr")?g.inject(f,"after"):g.inject(l,"top");break;case"delete":d.length>1&&g.destroy()}for(d=l.getChildren(),i=0;i<d.length;i++)for(c=d[i].getChildren(),s=0;s<c.length;s++)(o=c[s].getFirst("a.chzn-single"))&&o.set("tabindex",u++),(a=c[s].getFirst("select"))&&(a.name=a.name.replace(/\[[0-9]+]/g,"["+i+"]")),(r=c[s].getFirst('input[type="checkbox"]'))&&(r.set("tabindex",u++),r.name=r.name.replace(/\[[0-9]+]/g,"["+i+"]")),(r=c[s].getFirst('input[type="text"]'))&&(r.set("tabindex",u++),r.name=r.name.replace(/\[[0-9]+]/g,"["+i+"]"));new Sortables(l,{constrain:!0,opacity:.6,handle:".drag-handle"})}};