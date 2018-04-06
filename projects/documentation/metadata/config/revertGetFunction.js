var globalState = this.dispatcher.getGlobalState();
var id = globalState.getCurrentId();
var ns = globalState.getContent(id).ns;
var rev = globalState.getContent(id).rev;
var pType = globalState.getContent(id).projectType;
if(this.query){
  _ret=this.query;
}else{
  _ret="do=revert";
}
_ret+="&id="+ns + "&projectType="+pType + "&rev="+rev;

