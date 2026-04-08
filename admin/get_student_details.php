<?php
require_once('../configurations/configurations.php');

if(isset($_POST['id'])) {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    
    $query = "SELECT * FROM users WHERE id = '$id'";
    $result = mysqli_query($con, $query);
    $student = mysqli_fetch_assoc($result);
    
    if($student) {
        // Subject config: label, color, icon, and category columns
        $subjects = [
            'english' => [
                'label' => 'English',
                'color' => '#0A5F38',
                'icon'  => 'fas fa-language',
                'categories' => [
                    'Grammar & Language Structure' => 'english_grammar_level',
                    'Vocabulary'                   => 'english_vocabulary_level',
                    'Reading Comprehension'        => 'english_reading_level',
                    'Literature'                   => 'english_literature_level',
                    'Writing Skills'               => 'english_writing_level',
                ],
            ],
            'ap' => [
                'label' => 'Araling Panlipunan (AP)',
                'color' => '#1565C0',
                'icon'  => 'fas fa-globe-asia',
                'categories' => [
                    'Ekonomiks'                  => 'ap_ekonomiks_level',
                    'Kasaysayan ng Pilipinas'     => 'ap_kasaysayan_level',
                    'Kontemporaryong Isyu'        => 'ap_kontemporaryo_level',
                    'Heograpiya'                  => 'ap_heograpiya_level',
                    'Pamahalaan at Lipunan'       => 'ap_pamahalaan_level',
                ],
            ],
            'filipino' => [
                'label' => 'Filipino',
                'color' => '#6A1B9A',
                'icon'  => 'fas fa-book',
                'categories' => [
                    'Gramatika'          => 'filipino_gramatika_level',
                    'Panitikan'          => 'filipino_panitikan_level',
                    'Pag-unawa sa Binasa'=> 'filipino_paguunawa_level',
                    'Talasalitaan'       => 'filipino_talasalitaan_level',
                    'Wika at Kultura'    => 'filipino_wika_level',
                ],
            ],
            'science' => [
                'label' => 'Science',
                'color' => '#00838F',
                'icon'  => 'fas fa-flask',
                'categories' => [
                    'Biology'                 => 'science_biology_level',
                    'Chemistry'               => 'science_chemistry_level',
                    'Physics'                 => 'science_physics_level',
                    'Earth Science'           => 'science_earthscience_level',
                    'Scientific Investigation'=> 'science_investigation_level',
                ],
            ],
            'math' => [
                'label' => 'Mathematics',
                'color' => '#E65100',
                'icon'  => 'fas fa-calculator',
                'categories' => [
                    'Algebra'             => 'math_algebra_level',
                    'Geometry'            => 'math_geometry_level',
                    'Statistics'          => 'math_statistics_level',
                    'Probability'         => 'math_probability_level',
                    'Functions & Equations'=> 'math_functions_level',
                    'Word Problems'       => 'math_wordproblems_level',
                ],
            ],
        ];
        ?>
        <div class="row">
            <!-- Student Info -->
            <div class="col-md-4 text-center">
                <div class="student-avatar" style="width: 80px; height: 80px; font-size: 2rem; margin: 0 auto 20px;">
                    <?php echo strtoupper(substr($student['player_name'], 0, 1)); ?>
                </div>
                <h4><?php echo htmlspecialchars($student['player_name']); ?></h4>
                <p class="text-muted"><?php echo htmlspecialchars($student['username']); ?></p>
                <p class="text-muted"><small>ID: <span class="badge badge-secondary"><?php echo htmlspecialchars($student['student_id']); ?></span></small></p>
                <p class="text-muted"><small>Registered: <?php echo date('M j, Y', strtotime($student['created_at'])); ?></small></p>

                <div class="row mt-3">
                    <div class="col-6 text-center">
                        <div class="border rounded p-2">
                            <div style="font-size:1.3rem;color:#e74a3b;"><i class="fas fa-heart"></i></div>
                            <strong><?php echo $student['lives']; ?></strong><br><small>Lives</small>
                        </div>
                    </div>
                    <div class="col-6 text-center">
                        <div class="border rounded p-2">
                            <div style="font-size:1.3rem;color:#f6c23e;"><i class="fas fa-feather"></i></div>
                            <strong><?php echo $student['feathers']; ?></strong><br><small>Feathers</small>
                        </div>
                    </div>
                </div>
                <?php if(!empty($student['selected_character'])): ?>
                <div class="mt-3">
                    <span class="badge badge-primary"><?php echo htmlspecialchars($student['selected_character']); ?></span>
                    <br><small class="text-muted">Character</small>
                </div>
                <?php endif; ?>
            </div>

            <!-- Progress -->
            <div class="col-md-8">
                <h6 class="mb-3"><i class="fas fa-chart-bar"></i> Subject &amp; Category Progress</h6>
                <?php
                $max_per_subject = 50; // 5 categories × 10 levels
                foreach($subjects as $key => $info):
                    $subject_sum = 0;
                    foreach($info['categories'] as $col) {
                        $subject_sum += isset($student[$col]) ? (int)$student[$col] : 0;
                    }
                    $pct     = round(($subject_sum / $max_per_subject) * 100);
                    $is_done = ($subject_sum === $max_per_subject);
                ?>
                <div class="card mb-3">
                    <div class="card-header py-2" style="background-color: <?php echo $info['color']; ?>; color: white;">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong><i class="<?php echo $info['icon']; ?>"></i> <?php echo $info['label']; ?></strong>
                            <span class="badge badge-light" style="color: <?php echo $info['color']; ?>; font-size: 0.85rem;">
                                <?php if($is_done): ?>
                                    <i class="fas fa-check-circle"></i> Completed
                                <?php else: ?>
                                    <?php echo $subject_sum; ?>/<?php echo $max_per_subject; ?> &mdash; <?php echo $pct; ?>%
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="progress mt-1" style="height: 6px; background: rgba(255,255,255,0.3);">
                            <div class="progress-bar bg-white" style="width: <?php echo $pct; ?>%;"></div>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <?php foreach($info['categories'] as $cat_label => $col):
                            $cat_level = isset($student[$col]) ? (int)$student[$col] : 0;
                            $cat_pct   = ($cat_level / 10) * 100;
                        ?>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <small><?php echo htmlspecialchars($cat_label); ?></small>
                                <small style="color: <?php echo $info['color']; ?>; font-weight: bold;">
                                    <?php echo $cat_level; ?>/10 (<?php echo $cat_pct; ?>%)
                                </small>
                            </div>
                            <div class="progress" style="height: 7px;">
                                <div class="progress-bar" style="width: <?php echo $cat_pct; ?>%; background-color: <?php echo $info['color']; ?>;"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    } else {
        echo '<div class="alert alert-danger">Student not found!</div>';
    }
}
?>
