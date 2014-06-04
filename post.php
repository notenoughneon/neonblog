<?
require("lib/common.php");
$title = "Post - " . $config["siteTitle"];
$token = $_COOKIE["bearer_token"];
require("tpl/header.php");
?>

            <form class="form-horizontal" enctype="multipart/form-data" action="micropub.php" method="post">
                <input type="hidden" name="h" value="entry" />
                <input type="hidden" name="access_token" value="<? echo $token ?>" />
                <legend>Post</legend>

                <div class="form-group">
                    <label for="name" class="col-sm-2 control-label">Title:</label>
                    <div class="col-sm-10">
                        <input id="name" name="name" type="text" class="form-control" placeholder="Leave blank for note" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="reply-to" class="col-sm-2 control-label">Reply&nbsp;to:</label>
                    <div class="col-sm-10">
                        <input id="reply-to" name="in-reply-to" type="text" class="form-control" placeholder="http://example.com/post-id" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="content" class="col-sm-2 control-label">Content:</label>
                    <div class="col-sm-10">
                        <textarea id="content" name="content" class="form-control" rows="8"></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">Syndicate&nbsp;to:</label>
                    <div class="col-sm-10">
                        <input id="twitter" name="syndicate-to" type="checkbox" value="twitter.com" />
                        <label for="twitter">Twitter</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="photo" class="col-sm-2 control-label">Photo:</label>
                    <div class="col-sm-10">
                        <input id="photo" name="photo" type="file" />
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-primary">Submit</input>
                    </div>
                </div>

            </form>

<? require("tpl/footer.php") ?>
