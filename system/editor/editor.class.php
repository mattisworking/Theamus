<?php

class editor {
    protected $Theamus;

	public function __construct($t) {
        $this->Theamus = $t;
        return;
	}

    public function load($options = array()) {
        $this->options($options);
        $this->show_editor();
        return;
    }

    public function options($options) {
        $this->var['code']      = isset($options['code']) ? $options['code'] : true;
        $this->var['images']    = isset($options['images']) ? $options['images'] : true;
        $this->var['sink']      = isset($options['sink']) ? $options['sink'] : true;
        $this->var['width']     = isset($options['width']) ? $options['width']."px" : "auto";
        $this->var['text']      = isset($options['text']) ? $options['text'] : "";
        $this->var['id']        = isset($options['id']) ? $options['id'] : "editor";
        return;
    }

    private function set_listeners() {
        ?>
        <script>
            $(document).ready(function() {
                theamus.editor.initialize({
                    id: "<?=$this->var['id']?>",
                    width: "<?=$this->var['width']?>"
                });
            });
        </script>
        <?php
        return;
    }

    private function print_executable_options($array) {
        $ret = array();
        foreach ($array as $a) {
            if (isset($a['option']) && $this->var[$a['option']] == false) continue;
            if (isset($a['separator'])) $ret[] = "<span class='rta-separator'></span>";
            else {
                if (isset($a['exec']))
                    $ret[] = "<a href='#' id='".$a['id']."' onclick=\"return rta.execute(this, '".$a['exec']."');\">";
                if (isset($a['oc']))
                    $ret[] = "<a href='#' id='".$a['id']."' onclick=\"return ".$a['oc']."\">";
                $ret[] = "<img src='system/rta/images/".$a['img']."' alt='' />";
                $ret[] = "</a>";
            }
        }
        return implode("", $ret);
    }

    private function get_executables() {
        $ret = array();
        return $ret;
    }

	public function show_editor() {
        $this->set_listeners();
        $style = $this->var['width'] != false ? "style='width: ".$this->var['width']."'" : "";
		?>
		<div id="editor_wrapper" style="width: <?=$this->var['width']?>">
            <div id="editor_link-info-wrapper"></div>
            <div id="editor_window-wrapper"></div>
            <div class="editor_format-options"><?=$this->get_sink()?></div>
            <div name="editor" class="editor_fancy-input" id="<?=$this->var['id']?>"><?=$this->var['text']?></div>
            <textarea name="editor_code" class="editor_code-input" id="<?=$this->var['id']?>-code"></textarea>
            <div class="editor_breadcrumbs">
                <span class="path">Path: </span>
                <span id="editor_breadcrumbs" style="width:10px;">Editor</span>
            </div>
		</div>
		<?php
	}

    private function get_sink() {
        if ($this->var['sink']) {
        ?>
        <select class="editor_executable" id="editor_font-size" data-exec="fontsize" data-click="false">
            <option value="normal">Normal</option>
            <option value="1">Heading 1</option>
            <option value="2">Heading 2</option>
            <option value="3">Heading 3</option>
            <option value="4">Heading 4</option>
            <option value="5">Heading 5</option>
        </select>
        <span class="editor_separator"></span>
        <a href="#" class="editor_executable editor_bold" data-exec="bold"></a>
        <a href="#" class="editor_executable editor_underline" data-exec="underline"></a>
        <a href="#" class="editor_executable editor_italicize" data-exec="italicize"></a>
        <span class="editor_separator"></span>
        <a href="#" class="editor_executable editor_unordered" data-exec="unorderedlist"></a>
        <a href="#" class="editor_executable editor_ordered" data-exec="orderedlist"></a>
        <span class="editor_separator"></span>
        <a href="#" class="editor_executable editor_justify-left" data-exec="alignleft"></a>
        <a href="#" class="editor_executable editor_justify-center" data-exec="aligncenter"></a>
        <a href="#" class="editor_executable editor_justify-right" data-exec="alignright"></a>
        <span class="editor_separator"></span>
        <a href="#" class="editor_executable editor_link" data-exec="addlink"></a>
        <?php if ($this->var['images']): ?>
        <span class="editor_separator"></span>
        <a href="#" class="editor_executable editor_image" data-exec="addimage"></a>
        <?php endif; ?>
        <span class="editor_separator"></span>
        <a href="#" class="editor_executable editor_sink" data-exec="showmore"></a>
        <div class="editor_sink-wrapper" id="editor_sink-wrapper">
            <a href="#" class="editor_executable editor_togglecode" data-exec="togglecode"></a>
            <?php if ($this->var['code']): ?>
            <span class="editor_separator"></span>
            <a href="#" class="editor_executable editor_codeblock" data-exec="addcodeblock"></a>
            <?php endif; ?>
            <span class="editor_separator"></span>
            <a href="#" class="editor_executable editor_justify-justify" data-exec="alignfull"></a>
            <span class="editor_separator"></span>
            <a href="#" class="editor_executable editor_indent" data-exec="indent"></a>
            <a href="#" class="editor_executable editor_outdent" data-exec="outdent"></a>
        </div>
        <?php
        }
    }
}