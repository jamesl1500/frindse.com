<style>
    .timelineBanner{
        background: url(<?php echo APP_URL; ?>users/data/<?php echo $_SESSION['salt']; ?>/banner);
    }
</style>
<div class="timelineBanner clearfix col-lg-12 col-md-12 col-sm-12">
    <div class="cover">
        <h3>Timeline</h3>
    </div>
</div>
<div class="timelineNav clearfix col-lg-12">
    <div class="leftNavBar col-lg-4">
        <ul>
            <li class="active"><a href="">Feed</a></li>
            <li><a href="">Popular</a></li>
            <li><a href="">Activity</a></li>
        </ul>
    </div>
    <div class="rightContent col-lg-8">
        <div class="userInfoBox col-lg-4 pull-right">
            <div class="leftIcon col-lg-2 pull-left">
                <img src="<?php echo APP_URL; ?>users/data/<?php echo $_SESSION['salt']; ?>/profile_picture" />
            </div>
            <div class="rightInfo col-lg-10">
                <div class="topName">
                    <h3><?php echo Users::get('users', $_SESSION['salt'], 'firstname'); ?> <?php echo Users::get('users', $_SESSION['salt'], 'lastname'); ?></h3>
                </div>
                <div class="bottomUsername">
                    <h3>@<?php echo Users::get('users', $_SESSION['salt'], 'username'); ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="timelineMainContainer clearfix">
    <div class="timelineFeedContParent col-lg-9">
        <div class="timelineFeedContChild" id="timelineFeedContChild">
            <div class="postingStationMain">
                <div class="postingStation item clearfix" style='margin: 0px;'>
                    <form action="" method="post" id="postingForm" onSubmit="return false;">
                        <div class="middlePostingStation">
                            <textarea id="timelinePostingStationBody" placeholder="Write something cool"></textarea>
                        </div>
                        <div class="extras">
                            <div class="allPhotos" style="padding: 0px;">
                                <img src="" style="margin: 5px;" height="100" width="100" id="previewImg" class="hidden"/>
                            </div>
                            <div class="videoPreview hidden" style="padding: 0px;">
                                <video style="width: 100%;height: auto;" src="" id="videoPreviewCont" controls>
                                    Your browser does not support the video tag.
                                </video>
                                <div class="video_progress">
                                    <progress class="progress" style="padding: 5px;background: #4aaee7;width: 100%;color: red;">

                                    </progress>
                                </div>
                            </div>
                            <div class="videoUploader" style="display: none;">
                                <input type="text" id="videoLink" style="font-size: 14px;margin: 0;border: none; border-top: 1px dotted #ddd;border-radius: 0px;padding: 5px;" placeholder="YouTube Link(Optional)" />
                            </div>
                        </div>
                        <div class="bottomPostingStation clearfix">
                            <div class="leftAssets col-lg-3">
                                <ul>
                                    <li>
                                        <label class="filebutton">
                                            <i class="fa fa-camera fa-fw"></i>
                                            <span><input type="file" id="timelinePhotoSelect" name="timelinePhotoSelect[]" style="width:10%;" accept="image/*" capture="" multiple="multiple"></span>
                                        </label>
                                    </li>
                                    <li style="padding-left: 0px;padding-right: 0px;">
                                        <label class="filebutton">
                                            <i class="fa fa-film fa-fw"></i>
                                            <span><input type="file" id="timelineVideoSelect" name="timelineVideoSelect[]" style="width:10%;" accept="video/*" capture=""></span>
                                        </label>
                                    </li>
                                    <li id="open" style="display: none;">
                                        <i class="fa fa-fw fa-youtube-play"></i>
                                    </li>
                                </ul>
                            </div>
                            <div class="rightAssets col-lg-9">
                                <div class="lastAssets pull-right">
                                    <input type="hidden" id="def-privacy" value="2" />
                                    <input type="hidden" id="upt" value="<?php echo Sessions::get('salt'); ?>" />
                                    <button type="submit" id="timelineBtn" class="btn primaryBTN" value="Post" style="float: right;">Post</button>
                                </div>
                                <div class="privacyList pull-right">
                                    <div id="dd" class="wrapper-dropdown-1" tabindex="1">
                                        <div class="wrapper-drop-head openPrivTab">
                                            <i class="fa fa-eye" aria-hidden="true"></i> <span class="currentSetting" style="color: #333;font-weight: 400;">Friends</span> <span class="priv-carrot"><i class="fa fa-caret-down"></i></span>
                                        </div>
                                        <ul class="dropdown hidden privacyDrop">
                                            <li class="privTabSetting" data-priv="1" data-val="Public">
                                                <h3><i class="fa fa-globe fa-fw"></i> Public</h3>
                                                <div>Everyone can see this</div>
                                            </li>
                                            <li class="privTabSetting privActive" data-priv="2" data-val="Friends">
                                                <h3><i class="fa fa-users fa-fw"></i> Friends</h3>
                                                <div>Only your friends</div>
                                            </li>
                                            <li class="privTabSetting" data-priv="3" data-val="Only me">
                                                <h3><i class="fa fa-lock fa-fw"></i> Only me</h3>
                                                <div>Only you can see this</div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div id="timelinePostsHold" class="clearfix" data-columns>
                <?php
                $posts = new Posts();
                $posts->loadTimeline(array('uid'=>Sessions::get('salt'), 'postnumbers'=>'10', 'offset'=>'0', 'return'=>'html'));
                ?>
            </div>
        </div>
    </div>
    <div class="timelineSidebarRight col-lg-3 col-md-3">
        <div class="box card-material card-default">
            <div class="cardHead">
                <h3>Suggestions</h3>
            </div>
            <?php Suggestions::getRandomUsers(); ?>
        </div><br />
        <div class="box card-material card-default">
            <div class="cardHead">
                <h3>Trends</h3>
            </div>
            <ul style="padding: 15px;margin: 0px;">
                <?php
                $db = new Database;

                $query = $db->prepare("SELECT * FROM ".HASHTAGS." ORDER BY used DESC LIMIT 3");
                $query->execute();

                if($query->rowCount() > 0)
                {
                    while($fetch =  $query->fetch(PDO::FETCH_ASSOC))
                    {
                        $name = $fetch['hashtag_name'];
                        $used = $fetch['used'];
                        $n = str_replace('#','',$name);
                        ?>
                            <li class="clearfix" style="padding: 3px;"><a href="<?php echo APP_URL; ?>search/<?php echo $n; ?>"><?php echo $name; ?></a></li>
                        <?php
                    }
                }
                ?>
            </ul>
        </div>
    </div>
</div>