<?php

class VIEW_JOIN_BASE extends VIEW
{
    protected function view_render_meter_detail_inputfield($meter_key, $caption, $id, $integrity, $style=null, $default=null)
    {
        $prefill = (isset($_SESSION['meters']["$meter_key"]["$id"]['value'])) ? $_SESSION['meters']["$meter_key"]["$id"]['value'] : '';
        if($prefill == '' && $default)
        {
            $prefill = $default;
            $_SESSION['meters']["$meter_key"]["$id"]['value'] = $default;
        }
        $_SESSION['meters']["$meter_key"]["$id"]['integrity'] = $integrity;
        print '<div style="padding:8px;' . $style . '">' . $caption . '<br><input type="text" name="' . $id . '_' . $meter_key . '" value="' . $prefill . '" id="' . $id . '_' . $meter_key . '" onfocus="this.select()" onfocusout="JaxonInteractives.update_meter_detail(' . "'" . $meter_key . "'" . ', ' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . '_' . $meter_key . "'" . ').value);" /></div>';
    }

    protected function view_render_meter_detail_explained_inputfield($meter_key, $caption, $id, $integrity, $style=null, $default=null, $unit=null, $explanation=null, $autocheckbox=false)
    {
        $prefill = (isset($_SESSION['meters']["$meter_key"]["$id"]['value'])) ? $_SESSION['meters']["$meter_key"]["$id"]['value'] : '';
        if($prefill == '' && $default)
        {
            $prefill = $default;
            $_SESSION['meters']["$meter_key"]["$id"]['value'] = $default;
        }

        $_SESSION['meters']["$meter_key"]["$id"]['integrity'] = $integrity;

        print '
            <br />
            <div id="container-' . $id . '">' . $caption . '<br>
                <div class="input-box" style="width:240px;">
                    <input type="text" name="' . $id . '_' . $meter_key . '" id="' . $id . '_' . $meter_key . '" placeholder="' . $prefill . '" value="' . $prefill . '" maxlength="6" style="width:85px;text-align:center" onfocus="this.select()" onfocusout="JaxonInteractives.update_meter_detail(' . "'" . $meter_key . "'" . ', ' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . '_' . $meter_key . "'" . ').value);" />
                    <span class="prefix">' . $unit . '&nbsp;&nbsp;&nbsp;</span>
        ';

        if($explanation)
        {
            print "<div class=\"help-container\" style=\"float:right\" tabindex=\"0\">
                                <span class=\"help-icon\">?</span>
                                <div class=\"help-box\">
                                    $explanation 
                                </div>
                           </div>
                           <div class=\"help-overlay\"></div>
                    ";
        }

        print '
                </div>
        ';

        if($autocheckbox === true)
        {
            print "<div style=\"font-weight:normal;font-size:12pt\"><input type=\"checkbox\" id=\"" . $id . "_" . $meter_key . "_unknown\" style=\"width:18px;height:18px;margin-top:12px;vertical-align:bottom !important\" onchange=\"if(this.checked){document.getElementById('$id" . '_' . $meter_key . "').value=0;JaxonInteractives.update_meter_detail('$meter_key', '$id', '0');}else{document.getElementById('$id" . "_" . $meter_key . "').value='';JaxonInteractives.update_meter_detail('$meter_key', '$id', '');}\">
            Angabe ist mir nicht bekannt</div><br />";
        }
        print '
                <br />
            </div>
        ';
    }

    protected function view_render_part_captioned_inputfield($caption, $id, $session_bucket=null, $integrity=null, $style=null, $placeholder=null)
    {
        if($session_bucket != null)
        {
            if(isset($_SESSION["$session_bucket"]["$id"]))
            {
                $prefill = isset($_SESSION["$session_bucket"]["$id"]["value"]) ? $_SESSION["$session_bucket"]["$id"]["value"] : '';
            }
            else
            {
                $_SESSION["$session_bucket"]["$id"]["integrity"] = $integrity;
                $prefill = '';
            }

            print '<div style="padding:8px;line-height:40px;' . $style . '">' . $caption . '<input type="text" onfocus="this.select()" name="' . $id . '" id="' . $id . '" placeholder="' . $placeholder . '" value="' . $prefill . '" onfocusout="JaxonInteractives.update_session_bucket(' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . "'" . ').value, ' . "'" . $session_bucket . "'" . ');" /></div>';

            if($integrity == 'iban')
            {
                print '
                    <script>
                        function formatIbanInput(input) {
                          let value = input.value;
                        
                          // Remove all dashes and spaces
                          value = value.replace(/[-\s]/g, \'\');
                        
                          // Group the string into chunks of 4 characters separated by spaces
                          let formattedValue = value.replace(/(.{4})/g, \'$1 \').trim();
                        
                          // Update the input box with the modified value
                          input.value = formattedValue;
                        }
                        
                        document.getElementById(\'' . $id . '\').addEventListener(\'input\', function() {
                            formatIbanInput(this);
                        });
                    </script>
                ';
            }
            elseif($integrity == 'hwinventoryid')
            {
                print '
                    <script>
                        function formatHwInput(input) {      

                            // removes all non-numbers
                            var number = input.value.replace(/\D/g, \'\');
                            
                            // limits to 9 digits
                            if (number.length > 9) {
                                number = number.slice(0, 9);
                            }
                            
                            // formatting the value to xxx.xxx.xxx
                            var formattedNumber = \'\';
                            for (var i = 0; i < number.length; i += 3) {
                                if (i > 0) {
                                    formattedNumber += \'.\';
                                }
                                formattedNumber += number.slice(i, i + 3);
                            }
                            
                            // Update the input box with the modified value
                            input.value = formattedNumber;
                        }
                        
                        document.getElementById(\'' . $id . '\').addEventListener(\'input\', function() {
                            formatHwInput(this);
                        });
                    </script>
                ';
                
            }

        }
        else
        {
            print '<div style="' . $style . '">' . $caption . '<input type="text" onfocus="this.select()" name="' . $id . '" id="' . $id . '" /></div>';
        }
    }

    protected function view_render_part_captioned_select($caption, $id, $arrOptions, $session_bucket=null, $integrity=null, $style=null)
    {
        if($session_bucket != null)
        {
            if(isset($_SESSION["$session_bucket"]["$id"]))
            {
                $preselect = isset($_SESSION["$session_bucket"]["$id"]["value"]) ? $_SESSION["$session_bucket"]["$id"]["value"] : '';
            }
            else
            {
                $_SESSION["$session_bucket"]["$id"]["integrity"] = $integrity;
                $preselect = '';
            }

            print '<div style="padding:8px;line-height:40px;' . $style . '">' . $caption . '
                    <select name="'. $id . '" id="' . $id . '" value="' . $preselect . '" onchange="JaxonInteractives.update_session_bucket(' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . "'" . ').value, ' . "'" .  $session_bucket . "'" . ');" />';

            print "<option value=''>-</option>";

            foreach($arrOptions as $option => $value)
            {
                if(!$value)
                {
                    $value = $option;
                }
                if($preselect == $option) $selected = 'selected';   else $selected = '';
                print "<option $selected value='" . $option . "'>" . $value . "</option>";
            }

            print '</select></div>';
        }
        else
        {
            print '<div style="' . $style . '">' . $caption . '
                    <select name="' . $id . '" id="' . $id . '" />';
            print "<option value=''>-</option>";

            foreach($arrOptions as $option)
            {
                print "<option value='" . $option . "'>" . $option . "</option>";
            }

            print '</select></div>';
        }
    }

    protected function view_render_part_annotated_checkbox($annotation, $id, $session_bucket=null, $integrity=null)
    {
        $checked = '';
        if(isset($_SESSION["$session_bucket"]["$id"]))
        {
            if($_SESSION["$session_bucket"]["$id"]['value'] != '')
            {
                $checked = 'checked';
            }
        }

        $_SESSION["$session_bucket"]["$id"]['integrity'] = $integrity;
        print '<div style="display: flex; align-items: center;"><input ' . $checked . ' type="checkbox" name="' . $id . '" id="' . $id . '" onchange="JaxonInteractives.update_session_bucket(' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . "'" . ').checked, ' . "'" .  $session_bucket . "', true" . ');" /><label for="' . $id . '" style="margin-top:16px;margin-left:12px;line-height: 24px;">' . $annotation . '</label></div>';

    }

    protected function view_render_prefilled_meter($caption, $prefill, $id)
    {
        $remaining_characters = 33 - strlen(str_replace(' ', '', $prefill));
        print '
            <div id="container-' . $id . '">' . $caption . '<br>
                <div class="input-box" style="float:left">
                    <input type="text" name="' . $id . '" id="' . $id . '" value="' . $prefill . '" maxlength="37" oninput="document.getElementById(\'counter_' . $id . '\').textContent=33 - this.value.replace(/\s+/g,\'\').length" onfocus="this.setSelectionRange(this.value.length, this.value.length)" onfocusout="JaxonInteractives.update_meter_value(' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . "'" . ').value);" />
                    <button style="background-color:darkred" onclick="JaxonInteractives.rmv_meter(' . "'" . $id . "'" . ');"><i style="font-size:16px;color:white;" class="icon fa-trash-alt"></i></button><br />
                </div>
                &nbsp; <span id="counter_' . $id . '">' . $remaining_characters . '</span> Stelle(n) verbleiben
                &nbsp;<br />
                <br style="clear:both" />
            </div>';
    }
    protected function view_render_prefixed_meter($caption, $prefix, $id, $value="0000000")
    {
        print '
            <div id="container-' . $id . '">' . $caption . '<br>
                <div class="input-box">
                    <span class="prefix">' . $prefix . '</span>
                    <input type="text" name="' . $id . '" id="' . $id . '" value="' . $value . '" maxlength="7" onfocus="this.setSelectionRange(this.value.length, this.value.length)" onfocusout="JaxonInteractives.update_meter_value(' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . "'" . ').value);" />
                    <button style="background-color:darkred" onclick="JaxonInteractives.rmv_meter(' . "'" . $id . "'" . ');"><i style="font-size:16px;color:white;" class="icon fa-trash-alt"></i></button><br>
                </div>
                <br />
            </div>';
    }

    protected function view_render_prefixed_storage($caption, $id, $value=0)
    {
        print '
            <div id="container-' . $id . '">' . $caption . '<br>
                <div class="input-box" style="width:254px;">
                    <span class="prefix">Kapazit&auml;t:&nbsp;</span>
                    <input type="text" name="' . $id . '" id="' . $id . '" value="' . $value . '" maxlength="6" style="width:65px;text-align:center" onfocus="this.select()" onfocusout="JaxonInteractives.update_storage_value(' . "'" . $id . "'" . ', document.getElementById(' . "'" . $id . "'" . ').value);" />
                    <span class="prefix">kWh</span>
                    &nbsp;&nbsp;&nbsp;&nbsp;<button style="background-color:darkred"  onclick="JaxonInteractives.rmv_storage(' . "'" . $id . "'" . ');"><i style="font-size:16px;color:white;" class="icon fa-trash-alt"></i></button><br>
                </div>
                <br />
            </div>';
    }

}
