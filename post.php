<?
require("lib/common.php");
$title = "Post - " . $config["siteTitle"];
require("tpl/header.php");
?>

            <form class="form-horizontal">
                <legend>Post</legend>

                <div class="form-group">
                    <label for="name" class="col-sm-2 control-label">Title:</label>
                    <div class="col-sm-10">
                        <input id="name" type="text" class="form-control" placeholder="Leave blank for note" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="reply-to" class="col-sm-2 control-label">Reply to:</label>
                    <div class="col-sm-10">
                        <input id="reply-to" type="text" class="form-control" placeholder="http://example.com/post-id" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="content" class="col-sm-2 control-label">Content:</label>
                    <div class="col-sm-10">
                        <textarea id="content" class="form-control" rows="8"></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-primary">Submit</input>
                    </div>
                </div>

            </form>

<? require("tpl/footer.php") ?>
