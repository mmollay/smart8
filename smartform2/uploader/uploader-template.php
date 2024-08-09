<div id="file-uploader">
    <div id="drop-zone" class="ui placeholder segment">
        <div class="ui icon header">
            <i class="file alternate outline icon"></i>
            <?php echo $translations['dropzone_text']; ?>
        </div>
    </div>
    <input type="file" id="file-input" multiple style="display: none;" accept="<?php echo implode(',', array_map(function ($format) {
        return '.' . $format;
    }, $config['ALLOWED_FORMATS'])); ?>">
    <div id="progress-container" style="display: none;">
        <div class="ui progress" data-percent="0" id="progress">
            <div class="bar">
                <div class="progress"></div>
            </div>
        </div>
    </div>
    <div class="ui relaxed divided list" id="file-list"></div>
    <button id="delete-all" class="ui red button"
        style="display: none;"><?php echo $translations['delete_all']; ?></button>
</div>