{if $rawstatus eq 'active'}
<link rel="stylesheet" href="modules/servers/fnextcloud/theme/style.css">
<link rel="stylesheet" href="modules/servers/fnextcloud/theme/flags.css">
    <div class="row m-b-15">
		<div class="col-md-6 col-sm-12">
			<h4>服务信息 <small>Service Detail</small></h4>
		</div>
	</div>
<div id="YVSY">	
	<div class="row">
        <div class="col-md-4 col-sm-12">
            <a href="javascript:;">
                <div class="box">
                    <div class="boxTitle">
                        产品名称
                    </div>
                    <div>
                        <span class="boxContent">{$product}</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-sm-12">
            <a href="javascript:;">
                <div class="box">
                    <div class="boxTitle">
                        产品状态
                    </div>
                    <div>
                        <span class="boxContent">{$status}</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-sm-12">
            <a href="javascript:;">
                <div class="box">
                    <div class="boxTitle">
                        到期时间
                    </div>
                    <div>
                        <span class="boxContent">{$nextduedate}</span>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
    <div class="row m-b-15">
	<div class="col-md-6 col-sm-12 pull-right">
		<form action="{$loginurl}" method="get" target="_blank">
		    <input type="hidden" value="jumpfrom" name="fnextcloud">
			<button type="submit" class="btn btn-default pull-right">
			<span class="glyphicon glyphicon-fire m-r-5" aria-hidden="true"></span> 前往NextCloud
			</button>
		</form>
        </div>
		<div class="col-md-6 col-sm-12">
            <h4>产品信息 <small>Product Detail</small></h4>
        </div>
    </div>
<div id="YVSY">	
    <div class="row">
        <div class="col-md-4 col-sm-12">
            <div class="box">
                <div class="boxTitle">
                    账户名称
                </div>
                <div>
                <span class="boxContent">{$username}</span>
                  </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-12">
            <div class="box">
                <div class="boxTitle">
                    账户密码
                </div>
                <div>
                  <span class="boxContent">{$password}</span>
                </div>
             </div>
        </div>
        <div class="col-md-4 col-sm-12">
            <div class="box">
                <div class="boxTitle">
                    已用配额(Gb)
                </div>
                <div>
                  <span class="boxContent">{$quota_used}</span>
                </div>
             </div>
        </div>
        <div class="col-md-4 col-sm-12">
            <div class="box">
                <div class="boxTitle">
                    总配额(Gb)
                </div>
                <div>
                  <span class="boxContent">{$quota_all}</span>
                </div>
             </div>
        </div>
	</div>
</div>
{else}
抱歉,该产品目前无法管理({$status})
{if $suspendreason}
,原因:{$suspendreason}
{/if}
{/if}		