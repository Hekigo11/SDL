<?php
$today = new DateTime('today');
$min_date = clone $today;
$min_date->modify('+4 days'); // Default 3 days advance (4 days from today)
$sug_dates = [];

// Generate 5 suggested dates starting from earliest available
for ($i = 0; $i < 5; $i++) {
    $date = clone $min_date;
    $date->modify("+$i days");
    $sug_dates[] = $date;
}
?>

<div class="form-group mb-4">
    <label><strong>Event Date & Time Selection</strong></label>
    
    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <label>Select Event Date Type</label>
                <div class="custom-control custom-radio">
                    <input type="radio" id="event_date_soon" name="event_date_type" value="next_available" class="custom-control-input" checked>
                    <label class="custom-control-label" for="event_date_soon">Next Available Date <small class="text-muted">(Based on group size)</small></label>
                </div>
                <div class="custom-control custom-radio">
                    <input type="radio" id="event_date_specific" name="event_date_type" value="specific_date" class="custom-control-input">
                    <label class="custom-control-label" for="event_date_specific">Select Specific Date</label>
                </div>
            </div>
            
            <!-- Next Available Date Options -->
            <div id="next_available_container" class="mt-3">
                <label class="mb-2">Recommended Dates:</label>
                <div class="row">
                    <?php foreach ($sug_dates as $idx => $date): ?>
                    <div class="col-md-6 mb-2">
                        <div class="custom-control custom-radio">
                            <input type="radio" id="quick_date_<?php echo $idx; ?>" 
                                name="quick_date" 
                                value="<?php echo $date->format('Y-m-d'); ?>" 
                                class="custom-control-input"
                                <?php echo ($idx === 0) ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="quick_date_<?php echo $idx; ?>">
                                <strong><?php echo $date->format('l, F j, Y'); ?></strong>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="form-group mt-3">
                    <label>Event Time</label>
                    <select class="form-control" name="quick_event_time" id="quick_event_time">
                        <?php
                        $times = array(
                            '09:00' => '9:00 AM',
                            '10:00' => '10:00 AM',
                            '11:00' => '11:00 AM',
                            '12:00' => '12:00 PM',
                            '13:00' => '1:00 PM',
                            '14:00' => '2:00 PM',
                            '15:00' => '3:00 PM',
                            '16:00' => '4:00 PM',
                            '17:00' => '5:00 PM',
                            '18:00' => '6:00 PM',
                            '19:00' => '7:00 PM'
                        );

                        foreach ($times as $value => $label) {
                            $selected = ($value === '13:00') ? 'selected' : '';
                            echo "<option value=\"$value\" $selected>$label</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <!-- Specific Date Selection -->
            <div id="specific_date_container" class="mt-3" style="display:none;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Event Date</label>
                            <?php 
                            $min_date_str = $min_date->format('Y-m-d');
                            
                            $max_date = clone $today;
                            $max_date->modify('+3 months');
                            $max_date_str = $max_date->format('Y-m-d');
                            ?>
                            <input type="date" 
                                class="form-control" 
                                name="event_date" 
                                id="event_date"
                                min="<?php echo $min_date_str; ?>"
                                max="<?php echo $max_date_str; ?>">
                            <small class="form-text text-muted" id="dateHelperText">
                                Please book at least 3 days in advance (14 days for 100+ persons).
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Event Time</label>
                            <select class="form-control" name="event_time" id="event_time">
                                <?php
                                $times = array(
                                    '08:00' => '8:00 AM',
                                    '09:00' => '9:00 AM',
                                    '10:00' => '10:00 AM',
                                    '11:00' => '11:00 AM',
                                    '12:00' => '12:00 PM',
                                    '13:00' => '1:00 PM',
                                    '14:00' => '2:00 PM',
                                    '15:00' => '3:00 PM',
                                    '16:00' => '4:00 PM',
                                    '17:00' => '5:00 PM',
                                    '18:00' => '6:00 PM',
                                    '19:00' => '7:00 PM',
                                    '20:00' => '8:00 PM'
                                );

                                foreach ($times as $value => $label) {
                                    $selected = ($value === '13:00') ? 'selected' : '';
                                    echo "<option value=\"$value\" $selected>$label</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
