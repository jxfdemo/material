
<!DOCTYPE html>
<html lang="en">

	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">

		<!--标准mui.css-->
		<link rel="stylesheet" href="../../../../css/mui.min.css">
		<link rel="stylesheet" href="../../../../css/iconfont.css" />
		<style>
			html,
			body,
			.mui-content {
				color: #888;
				background-color: #FFF;
			}
			
			.mui-slider .mui-segmented-control.mui-segmented-control-inverted~.mui-slider-group .mui-slider-item {
				border: none;
			}
			
			.list-status {
				position: absolute;
				right: 8%;
				width: 10% !important;
				top: 0;
			}
			
			img {
				border-radius: 50%;
				float: left;
				width: 45px;
				height: 45px;
			}
			
			span {
				float: left;
				width: 45px;
				height: 45px;
				margin-left: 5px;
			}
			
			.mui-table-view .mui-active {
				background-color: #FFF;
				!important
			}
			
			.datatom-tran {
				top: 10%;
				transform: rotate(-27deg);
				-ms-transform: rotate(-27deg);
				/* IE 9 */
				-moz-transform: rotate(-27deg);
				/* Firefox */
				-webkit-transform: rotate(-27deg);
				/* Safari 和 Chrome */
				-o-transform: rotate(-27deg);
				/* Opera */
			}
			
			.mui-ellipsis {
				font-size: .8rem;
			}
			
			.mui-pull-right-time {
				font-size: .8rem;
			}
			
			.mui-media-body {
				font-size: .75rem;
				color: #888;
				margin-top: 3px;
				margin-left: 10px;
			}
			
			.datatom-out {
				font-size: 34px;
				color: #555555;
			}
			
			.datatom-wait {
				font-size: 34px;
				color: #f9c909;
			}
			
			.datatom-pass {
				font-size: 34px;
				color: #39af6f;
			}
			
			.datatom-nopass {
				font-size: 34px;
				color: #ef5350;
			}
			.datatom-cancle{
				font-size: 34px;
				color: #8F8F94;
			}
		</style>
	</head>

	<body>
		<div class="mui-content ">
			<div id="slider" class="mui-slider mui-fullscreen">
				
				<div id="sliderProgressBar" class="mui-slider-progress-bar mui-col-xs-4"></div>
				<div class="mui-slider-group">
					<div id="item1mobile" class="mui-content mui-scroll-wrapper mui-slider-item mui-control-content mui-active">
						<div class="mui-scroll">
							<ul class="mui-table-view mui-table-view-chevron" id="kaoqin">

							</ul>
						</div>
					</div>
					
				</div>
			</div>
		</div>
		
		<script src="../../../../js/mui.min.js"></script>
		<script src="../../../../js/common.js" charset="UTF-8"></script>
		<script>
			mui.init({
				swipeBack: false
			});
			(function($) {
				var policenum = getPolicenum();
				var token = getToken();
				var currentDate = getNowFormatDate();
				var current = new Date(currentDate);
				Date.prototype.diff = function(date) {
					return(this.getTime() - date.getTime()) / (24 * 60 * 60 * 1000);
				}
				
				//我的申请-考勤
				mui('#item1mobile').pullRefresh({
					down: {
						contentrefresh: "正在加载...",
						callback: kqPullDownRefresh
					}
				});
				//我的考勤下拉刷新
				function kqPullDownRefresh() {
					mui.ajax(basePath + '/osapi/policeHelper.php', {
						data: {
							action: 'getNeedsApplyList',
							policenum: policenum,
						},
						beforeSend: function(request) {
							request.setRequestHeader("U-Auth-Token", token);
						},
						dataType: 'json', //服务器返回json格式数据
						type: 'POST', //HTTP请求类型
						timeout: 10000, //超时时间设置为10秒；
						success: function(data) {


							if(data.code == "200") {
								var list = data.result;
								var length = list.length;
								var ul = document.getElementById('kaoqin');
								var fragment = document.createDocumentFragment();
								ul.innerHTML = "";
								mui.each(list, function(index, item) {

									var apply_date = new Date(item.apply_time);
									var diff_count = current.diff(apply_date);
									var li = document.createElement('li');
									li.className = "mui-table-view-cell mui-media";
									var html = "";
									html += '<a class="mui-navigate-right" data-diff="' + diff_count + '" data-key="' + item.id + '"data-data="' + item.policenum + '"data-realname="' + item.realname +'"data-reason="' + item.reason+ '"data-starttime="' + item.starttime + '"data-endtime="' + item.endtime + '"data-leavetypestr="' + item.leavetypestr + '"data-day="' + item.day + '">';
									html += '<div style="width:45px;height:70px;"class="mui-pull-left" >';
									//
									html += '<img src="' + basePath + '/osapi/avantar.php?policenum=' + item.policenum + '"/>';
									html += '</div>';
									html += '<div class="mui-media-body">' + item.realname ;
									html += '<p class="mui-ellipsis">开始时间：' + item.starttime + '</p>'
									html += '<p class="mui-ellipsis">结束时间：' + item.endtime + '</p>'

									html += '</div></a>';
									li.innerHTML = html;
									fragment.appendChild(li);
								});
								ul.appendChild(fragment);
							} else {
								showWebviewToast(data.result);
							}
							mui('#item1mobile').pullRefresh().endPulldownToRefresh(); //refresh completed
						},
						error: function(xhr, type, errorThrown) {
							console.info(">>>错误");
							mui('#item1mobile').pullRefresh().endPulldownToRefresh(); //refresh completed
							showWebviewToast("网络异常!" + "xhr>>" + xhr + "type>>" + type + "errorThrown>>" + errorThrown);
						}
					});
				}
				

			

				mui.ready(function() {
					mui('#item1mobile').pullRefresh().pulldownLoading();
				});

				mui("#item1mobile").on("tap", "a", function() {
					var id = this.getAttribute('data-key');
					var policenum = this.getAttribute('data-data');
					var diff_count = this.getAttribute('data-diff');
					var realname=this.getAttribute('data-realname');
					var starttime=this.getAttribute('data-starttime');
					var endtime=this.getAttribute('data-endtime');
					var leavetypestr=this.getAttribute('data-leavetypestr');
					var day=this.getAttribute('data-day');
					var reason=this.getAttribute('data-reason');

					document.location.href = "../needApplyDetail.php?id=" + id + '&policenum=' + policenum + '&diff=' + diff_count+ '&realname=' + realname+'&reason='+reason+ '&starttime=' + starttime+ '&endtime=' + endtime+ '&leavetypestr=' + leavetypestr+ '&day=' + day;
					
				});

				setTitle('请假审批列表'); //设置title

			})(mui);
		</script>
	</body>

</html>