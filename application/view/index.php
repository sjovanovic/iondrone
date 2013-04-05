<!DOCTYPE html>
<html>
	<head>
		<style type="text/css">
		
		</style>
		<link rel="stylesheet" href="/styles/global.css" />

		<script src="/codemirror/lib/codemirror.js"></script>
		<link rel="stylesheet" href="/codemirror/lib/codemirror.css" />
		
		<script src="/codemirror/lib/util/dialog.js"></script>
		<link rel="stylesheet" href="/codemirror/lib/util/dialog.css" />

		<script src="/codemirror/lib/util/formatting.js"></script>
	        <script src="/codemirror/mode/css/css.js"></script>
	        <script src="/codemirror/mode/xml/xml.js"></script>
	        <script src="/codemirror/mode/javascript/javascript.js"></script>
	        <script src="/codemirror/mode/htmlmixed/htmlmixed.js"></script>
		<script src="/codemirror/mode/clike/clike.js"></script>
	        <script src="/codemirror/mode/php/php.js"></script>
		<script src="/codemirror/mode/htmlembedded/htmlembedded.js"></script>
		<script src="/codemirror/mode/markdown/markdown.js"></script>
		<script src="/codemirror/mode/gfm/gfm.js"></scrpt>



		<script src="/scripts/tabsui.js"></script>
		<script src="/scripts/proto.js"></script>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
		<script>
		var IonDrone = Proto.extend({
			'init':function(opts){
				this._super(opts);
				this.initDrone();
			},
			'initDrone':function(){
				this.params['user'] = this.uriToObject(this.opt.github_api+'/user');
				this.get({'access_token':this.opt.access_token}, 'user', function(){
					var user = this.user;
					var inst = this;
					this.apiUrl = user.data.url;
					var htm = '<div class="idrContainer"><div class="idrPanelOne"></div><div class="idrPanelTwo"><div class="idrEditor"></div></div><div class="idrPanelThree"></div></div>';
					$(document).ready(function(){
						inst.container = $(htm);
						if(inst.opt.container){
							$(inst.opt.container).html(inst.container);
						}else{
							$('body').html(inst.container);
						}
						inst.editor = CodeMirror(inst.container.find('.idrEditor:eq(0)').get(0), {
							"lineNumbers":true,
							"matchBrackets": true,
							"mode":"htmlmixed"
						});
						$(window).resize(function(){
							var hdr = inst.container.find('.idrPanelOne:eq(0)');
							var eh = $(window).height() - (hdr.offset().top + hdr.outerHeight());
							inst.container.find('.idrPanelTwo:eq(0),.idrPanelThree:eq(0),.idrEditor:eq(0),.CodeMirror-scroll:eq(0)').height(eh);
							inst.editor.refresh();
						});
						$(window).resize();
						inst.hookItUp();
						inst.getUserRepos();
					});
				});
			},
			'hookItUp':function(){
				var inst = this;
				this.header = inst.container.find('.idrPanelOne:eq(0)');
				this.browser = inst.container.find('.idrPanelThree:eq(0)');
				this.repospane = $('<div class="idrRepos"></div>');
				this.browser.html(this.repospane);
				this.toolbar = $('<div class="idrToolbar"></div>');
				this.tabs = $('<div class="idrTabs"></div>');
				this.login = $('<div class="idrLogin"></div>');

				var con = $('<div></div>');
				con.append(this.toolbar);
				con.append(this.tabs);
				con.append(this.login);			
				this.header.html(con);
				this.setUser();
				// key events
				$(window).keypress(function(event) {
					if (!(event.which == 115 && event.ctrlKey) && !(event.which == 19)) return true;					
					event.preventDefault();
					var fl = inst.activeFileLink;
					if(fl && fl.length){
						var des = fl.attr('rel').split('|');
						inst.newFile(des[0], des[1], inst.editor.getValue(), des[3], function(){
							this.editor.openConfirm('<div class="idrErr"><strong></strong>'+des[1]+' is saved to git repo.<button>Close</button></div>', []);
						});
					}
				});

			},
			'setToolbar':function(){
				this.toolbar.html('<button class="idrAutoformat">Autoformat</button><button class="idrComment">Comment</button><button class="idrUncomment">Uncomment</button>');
				this.toolbar.find('.idrAutoformat').bind('click.idr', function(){
					inst.autoFormatSelection();
				});
				this.toolbar.find('.idrComment').bind('click.idr', function(){
					inst.commentSelection(true);
				});
				this.toolbar.find('.idrUncomment').bind('click.idr', function(){
					inst.commentSelection(false);
				});

				

			},
			'setUser':function(){
				this.login.html('<a href="'+this.user.data.html_url+'"><img class="idrAvatar" src="'+this.user.data.avatar_url+'"/><div class="idrLoginName">'+this.user.data.login+'</div></a>');
				this
			},
			'autoFormatSelection':function(){
				var range = this.getSelectedRange();
        			this.editor.autoFormatRange(range.from, range.to);
			},
			'commentSelection':function(isComment){
				var range = this.getSelectedRange();
        			this.editor.commentRange(isComment, range.from, range.to);
			},
			'getSelectedRange':function(){
				return { from: this.editor.getCursor(true), to: this.editor.getCursor(false) };
			},
			'setMode':function(filepath){
				var fname = filepath;
				if(filepath.indexOf('/') != -1){
					fname = filepath.split('/');
					fname = fname[fname.length-1];
				}
				var filext = fname.split('.');
				filext = filext[filext.length-1];
				var mode = 'markdown'; 
				switch(filext){
					case "cpp":
					case "cc":
					case "hpp":
						mode = 'text/x-c++src';
						break;
					break;
					case "c":
					case "m":
					case "h":
					case "m":
						mode = 'text/x-csrc';
						break;
					case "java":
						mode = 'text/x-java';
						break;
					case "cs":
						mode = 'text/x-csharp';
						break;
					case "html":
					case "htm":
						mode = 'htmlmixed';
						break;
					case "js":
					case "as":
					case "es":
						mode = 'text/javascript';
						break;
					case "css":
						mode = 'text/css';
						break;
					case "json":
						mode = 'json';
						break;
					case "xml":
						mode = 'xml';
						break;
					case "php":
						mode = 'php';
						break;
					case "asp":
					case "aspx":
						mode = 'application/x-aspx';
						break;
					case "jsp":
						mode = 'application/x-jsp';
						break;
					case "ejs":
						mode = 'application/x-ejs';
						break;
					case "md":
					case "markdown":
					case "mdown":
					case "mdwn":
					case "txt":
					case "text":
						mode = 'markdown';
						break;
					case "sql":
						mode = 'text/x-mysql';
						break;
				}
				//console.log(mode+' '+filext);
				this.editor.setOption('mode', mode);
			},
			'getUserRepos':function(){
				var inst = this;
				this.params['repos'] = this.uriToObject(this.opt.github_api+'/user/repos');
				this.repospane.html('<div class="idrReposTools"><button class="idrNewRepo" title="Create New Repository">New Repository</button></div><div class="idrReposTree"></div>');
				this.repospane.find('.idrNewRepo:eq(0)').bind('click.idr', function(){
					var newrepo = {};
					inst.editor.openDialog('<b>How would you like to name your repository?</b>\
					<input type="text" value=""/>',
					function(rname){
						newrepo['name'] = rname;
						inst.createNewRepo(newrepo);
						/*inst.editor.openConfirm(msg, [fn])*/
					});
				});
				this.get({'access_token':this.opt.access_token}, 'repos', function(){
					if(this.repos.data && this.repos.data.length){
						var htm = '<ul class="idrRepoList">';
						for(var i in this.repos.data){
							var repo = this.repos.data[i];
							htm += '<li id="'+repo.name+'" class="idrRepoEntry"><a class="idrRepoLink" href="javascript:;">'+repo.name+'</a>&nbsp;<a class="idrRepoEdit" href="'+repo.html_url+'" target="_blank">edit</a></li>';
						}
						htm += '</ul>';
						this.repospane.find('.idrReposTree:eq(0)').html(htm);
						this.repospane.find('.idrRepoLink').bind('click.idr', function(){
							inst.listRepo($(this).text());
						});
					}
				});
			},
			'createNewRepo':function(repo){
				var inst = this;
				this.corsPost('/user/repos', repo, function(res){
					//eval('var res = '+res.responseText+';');
					if(res.errors && res.errors.length > 0){
						var msg = [];
						for(var i in res.errors){msg.push(res.errors[i].message);}
						inst.editor.openConfirm('<div class="idrErr"><strong>'+res.message+'</strong> <div>'+(msg.join('<br/>'))+'</div><button>Close</button></div>', []);
					}else{
						inst.getUserRepos();
					}
				});
			},
			'toJson':function(obj){
				if(!obj){return '';}
				var jar = [];
				var isArray = $.isArray(obj);
				for(var i in obj){
					var ent = isArray?'':'"'+i+'":';
					switch(typeof obj[i]){
						case "boolean":
						case "number":
						ent += obj[i];
						break;
						case "object":
						ent += this.toJson(obj[i]);
						break;
						case "string":
						ent += '"'+obj[i]+'"';
						break;
						default:
						continue;
					}
					jar.push(ent);
				}
				return (isArray?'[':'{')+jar.join(',')+(isArray?']':'}');
			},
			'corsGet':function(url, data, callback){
				if(typeof data === 'function'){
					return this.corsPost(url, {}, data, true);
				}else{
					return this.corsPost(url, data, callback, true);
				}
			},
			'corsPost':function(url, data, callback, isGet){
				var inst = this;
				url = url.match(/^http/)?url:this.opt.github_api+url+'?access_token='+this.opt.access_token;
				var tp = 'POST';
				if(isGet){
					tp = 'GET';
					var ps = [];
					for (i in data){
						ps.push(i+'='+data[i]);
					}
					url += '&'+ps.join('&');
					data = false;
				}	
				if(!this.objstorage){this.objstorage = {}}
				$.ajax({
					url: url,
					type: tp,
					data:this.toJson(data),
					success: function(resp){eval('var resp = '+resp); callback.call(inst, resp);},
					error:function(resp){eval('var resp = '+resp); callback.call(inst, resp);},
					crossDomain:true,
					xhrFields: {
						withCredentials: true
					}
				});
			},
			'listRepo':function(repo){
				//get first branch
				var inst = this;	// missing: GET /repos/:user/:repo
				this.corsGet('/repos/'+this.user.data.login+'/'+repo+'/branches', function(data){
					if(data && data.message){
						inst.editor.openConfirm('<div class="idrErr"><strong>'+data.message+'</strong> <button>Close</button></div>', []);
						return false;
					}
					if(data && data.length > 0 && data[0]){
/*{
    "name": "master",
    "commit": {
      "sha": "6dcb09b5b57875f334f61aebed695e2e4193db5e",
      "url": "https://api.github.com/octocat/Hello-World/commits/c5b97d5ae6c19d5c5df71a34c7fbeeda2479ccbc"
    }
}*/
						var branch = data[0];
						var commitsha = branch.commit.sha;
						this.repospane.find('#'+repo).attr('rel', commitsha); // last repo commit
						//set repo data
						if(!this.reposdata){this.reposdata = {};}
						inst.reposdata[repo] = {
							"lastCommit":commitsha
						};

						//GET /repos/:user/:repo/git/trees/:sha
						this.corsGet('/repos/'+this.user.data.login+'/'+repo+'/git/trees/'+commitsha, {'recursive':1}, function(res){
/*
"tree":[{ "type": "blob", "url": "https://api.github.com/repos/sjovanovic/iondrone/git/blobs/c3104bef8a729de9d6f4a2c2257d607682d639fa", "size": 4010, "path": "www_root/styles/global.css", "sha": "c3104bef8a729de9d6f4a2c2257d607682d639fa", "mode": "100644" }]
var chr = '&#9500;' '&#9492;'
*/
							if(res.sha){inst.reposdata[repo].baseTree = res.sha;}
							var tree = this.genRepoTree(res, repo);
							tree.find('ul').hide();
							var rpr = this.repospane.find('#'+repo);
							rpr.find('.idrTree').remove();
							rpr.append(tree);
							//open file
							rpr.find('.idrFileLink').bind('click.idr', function(){
								var des = $(this).attr('rel').split('|');
								var el = $(this);
								inst.getFile(des[0], des[2], function(content){
									this.setMode(des[1]);
									inst.activeFileLink = el;
									this.editor.setValue(content);
								});
							});
							//open dir
							rpr.find('.idrDirLink').bind('click.idr', function(){
								var el = $(this);
								$(this).parent().find('ul:eq(0)').toggle(0, function(){
									inst.editor.openConfirm('<div class="idrTools"><strong>'+$(this).parent().attr('title')+'</strong> <button>Cancel</button><button>New File</button></div>', [function(){}, function(){
										//new file
										//iondrone|www_root/scripts|2a2a72d7d88651e5d356d6ba4f50265c486df0da|tree
										var des = el.attr('rel').split('|');
										inst.editor.openDialog('<b>New file name?</b>\
										<input type="text" value=""/>',
										function(fname){
											inst.newFile(des[0], des[1]+'/'+fname, '', 'blob', function(){
												this.listRepo(repo);
											});
										});
									}]);
								});
							});
						});
					}
				});
			},
			'newFile':function(repo, path, fdata, type, callback){
				var inst = this;
				fdata = fdata?fdata:'';
				//POST /repos/:user/:repo/git/blobs
				this.corsPost('/repos/'+this.user.data.login+'/'+repo+'/git/blobs',
					{
						"content":Base64.encode(fdata),
						"encoding":"base64"
					},
					function(data){
/*
{ "url": "https://api.github.com/repos/sjovanovic/iondrone/git/blobs/c902940f206f08b53cb7cffdabd1de4043c471bd", "sha": "c902940f206f08b53cb7cffdabd1de4043c471bd" }
*/
						if(data && data.sha){
							this.createTree(repo, path, data.sha, this.reposdata[repo].baseTree, type, function(treedata){
								this.createCommit(repo, 'Adding new file: '+path, treedata.sha, [this.reposdata[repo].lastCommit], function(cdata){
									// update branch to point to the latest commit
									this.repospane.find('#'+repo).attr('rel', cdata.sha);
									this.reposdata[repo].lastCommit = cdata.sha;
									this.updateReference(repo, 'heads/master', cdata.sha, function(data){
										callback.call(this, data);
										this.listRepo(repo);
									});
								});
							});
						}else{
							this.editor.openConfirm('<div class="idrErr"><strong>Something went wrong while creating a blob. '+(data.message?data.message:'')+'</strong> <button>Close</button></div>', []);
						}
					}
				);
			},
			'getFile':function(repo, sha, callback){
				//GET /repos/:user/:repo/git/blobs/:sha
				this.corsGet('/repos/'+this.user.data.login+'/'+repo+'/git/blobs/'+sha, function(data){
					if(data && data.content){
						if(data.encoding == 'utf-8'){
							callback.call(this, data.content);
						}else{
							callback.call(this, Base64.decode(data.content));
						}
					}else{
						callback.call(this, '');
					}
				});
			},
			'createTree':function(repo, path, sha, baseTree, type, callback){
				type = type?type:'blob'; var mode = '100644';
				switch(type){
					case "tree":
						mode = '040000';
					break;
					case "submodule":
						mode = '160000';
					break;
					case "executable":
						mode = '100755';
					break;
					case "symlink":
						mode = 'symlink';
					break;
				}
				//POST /repos/:user/:repo/git/trees
/*{
  "sha": "cd8274d15fa3ae2ab983129fb037999f264ba9a7",
  "url": "https://api.github.com/repo/octocat/Hello-World/trees/cd8274d15fa3ae2ab983129fb037999f264ba9a7",
  "tree": [
    {
      "path": "file.rb",
      "mode": "100644",
      "type": "blob",
      "size": 132,
      "sha": "7c258a9869f33c1e1e1f74fbb32f07c86cb5a75b",
      "url": "https://api.github.com/octocat/Hello-World/git/blobs/7c258a9869f33c1e1e1f74fbb32f07c86cb5a75b"
    }
  ]
}
*/
				this.corsPost('/repos/'+this.user.data.login+'/'+repo+'/git/trees',
					{
					  "base_tree":baseTree,
					  "tree": [
					    {
					      "path": path,
					      "mode": mode,
					      "type": type,
					      "sha": sha
					    }
					  ]
					},
					function(data){
						if(data && data.sha){
							callback.call(this, data);
						}else{
							this.editor.openConfirm('<div class="idrErr"><strong>Something went wrong while creating a tree. '+(data.message?data.message:'')+'</strong> <button>Close</button></div>', []);
						}	
					}
				);
			},
			'createCommit':function(repo, message, treesha, parents, callback){
				// POST /repos/:user/:repo/git/commits
/*
{
  "sha": "7638417db6d59f3c431d3e1f261cc637155684cd",
  "url": "https://api.github.com/repos/octocat/Hello-World/git/commits/7638417db6d59f3c431d3e1f261cc637155684cd",
  "author": {
    "date": "2008-07-09T16:13:30+12:00",
    "name": "Scott Chacon",
    "email": "schacon@gmail.com"
  },
  "committer": {
    "date": "2008-07-09T16:13:30+12:00",
    "name": "Scott Chacon",
    "email": "schacon@gmail.com"
  },
  "message": "my commit message",
  "tree": {
    "url": "https://api.github.com/repos/octocat/Hello-World/git/trees/827efc6d56897b048c772eb4087f854f46256132",
    "sha": "827efc6d56897b048c772eb4087f854f46256132"
  },
  "parents": [
    {
      "url": "https://api.github.com/repos/octocat/Hello-World/git/commits/7d1b31e74ee336d15cbd21741bc88a537ed063a0",
      "sha": "7d1b31e74ee336d15cbd21741bc88a537ed063a0"
    }
  ]
}
*/
				var topost = {
					"message":message,
					"tree":treesha
				};
				if(parents && parents.length > 0){topost.parents = parents;}
				this.corsPost('/repos/'+this.user.data.login+'/'+repo+'/git/commits',
					topost,
					function(data){
						if(data && data.sha){
							callback.call(this, data);
						}else{
							this.editor.openConfirm('<div class="idrErr"><strong>Something went wrong while creating a commit. '+(data.message?data.message:'')+'</strong> <button>Close</button></div>', []);
						}	
					}
				);
			},
			'createReference':function(repo, ref, sha, callback){
				//POST /repos/:user/:repo/git/refs
				this.corsPost('/repos/'+this.user.data.login+'/'+repo+'/git/refs',
					{
						"ref":ref,
						"sha":sha
					},
					function(data){
						console.log(data);
						if(data && data.sha){
							callback.call(this, data);
						}else{
							this.editor.openConfirm('<div class="idrErr"><strong>Something went wrong while creating a reference. '+(data.message?data.message:'')+'</strong> <button>Close</button></div>', []);
						}
					}
				);
			},
			'updateReference':function(repo, ref, sha, callback){
				//PATCH /repos/:user/:repo/git/refs/:ref
				this.corsPost('/repos/'+this.user.data.login+'/'+repo+'/git/refs/'+ref,
					{
						"sha":sha,
						"force":false
					},
					function(data){
						console.log(data);
						if(data && data.ref){
							callback.call(this, data);
						}else{
							this.editor.openConfirm('<div class="idrErr"><strong>Something went wrong while updating a reference. '+(data.message?data.message:'')+'</strong> <button>Close</button></div>', []);
						}
					}
				);
			},
			'getFileTree':function(path, obj, entry){
				if(path == '' || !path){return {'_ion':entry};}
				path = path.split('/');
				if(path && path[0]){
					var npath = [];
					if(!obj[path[0]]){obj[path[0]] = {};}
					var npath = ''; var npt = [];
					if(path.length > 1){for(var i=1;i<path.length;i++){npt.push(path[i]);} npath = npt.join('/'); }
					obj[path[0]] = this.getFileTree(npath, obj[path[0]], entry);
				}
				return obj;
			},
			'getFileTreeHtml':function(obj, repo, hide){
				var htm = '', path = '', entry = {};
				// loop object if it is directory go recursive else end li
				for(var i in obj){
					if(i != '_ion'){
						var fl = (obj[i]['_ion'].type == 'blob');
						path = obj[i]['_ion'].path;
						path = path.split('/');
						fname = path[path.length-1];
						entry = obj[i]['_ion'];
						htm += '<li title="'+entry.path+'" class=""><a class="'+(entry.type=='blob'?'idrFileLink':'idrDirLink')+'" rel="'+repo+'|'+entry.path+'|'+entry.sha+'|'+entry.type+'" href="javascript:;">'+fname+'</a>';
						if(fl){
							htm += '</li>';
						}else{
							htm += '<ul>'+this.getFileTreeHtml(obj[i], repo, true)+'</ul></li>';
						}
						
					}
				}
				return htm;
			},
			'genRepoTree':function(trees, repo){
				var ob = {}, htm = '';
				if(trees && trees.tree && trees.tree.length){
					for(var i in trees.tree){
						var entry = trees.tree[i];
						this.getFileTree(entry.path, ob, entry);
					}
					htm = '<ul class="idrTree">'+this.getFileTreeHtml(ob, repo)+'</ul>';
	
				}
				return $(htm);
			}
		});
		</script>
		<script>
			var iondrone = new IonDrone({
				'access_token':'<?php echo $this->api->access_token(); ?>',
				'github_api':'https://api.github.com',
				'container':'#iondrone'
			});
/*
TabsUI.init({
	container:$('.menu'),
	tabContainerSelector:'.tabs',
	tabSelector:'.tab',
	tabs:[
	   {
	   	'value':'Item',
		'active':true,
	   	'action':function(evt){
			console.log('Item selected');
		}
	    }
	]
});
*/
		</script>
	</head>
	<body>
		<h1>Iondrone</h1>
		<div id="iondrone"></div>
	</body>
</html>
