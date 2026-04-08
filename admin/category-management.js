/**
 * Category Management JavaScript for Quiz System
 * Handles dynamic category dropdown display based on subject selection
 * 
 * @version 1.0.0
 * @date 2026-03-09
 */

$(document).ready(function() {
    console.log('Category Management System Initialized');
    
    // ========== ADD MODAL: Subject Change Handler ==========
    $('#add-subject-select').on('change', function() {
        const selectedSubject = $(this).val();
        console.log('Add Modal - Subject changed to:', selectedSubject);
        
        // Hide all category containers in add modal
        $('#addQuizModal .category-container').hide();
        $('#addQuizModal .category-select').prop('disabled', true).prop('required', false);
        
        // Show and enable the selected subject's category dropdown
        if (selectedSubject) {
            const categoryContainer = $('#add-' + selectedSubject + '-category');
            if (categoryContainer.length) {
                categoryContainer.show();
                categoryContainer.find('.category-select').prop('disabled', false).prop('required', true);
                console.log('✓ Showing category container for:', selectedSubject);
            } else {
                console.error('✗ Category container not found for:', selectedSubject);
            }
        }
    });
    
    // ========== EDIT MODAL: Subject Change Handler ==========
    $('#edit-subject-select').on('change', function() {
        const selectedSubject = $(this).val();
        console.log('Edit Modal - Subject changed to:', selectedSubject);
        
        // Hide all category containers in edit modal
        $('#editQuizModal .category-container').hide();
        $('#editQuizModal .category-select').prop('disabled', true).prop('required', false);
        
        // Show and enable the selected subject's category dropdown
        if (selectedSubject) {
            const categoryContainer = $('#edit-' + selectedSubject + '-category');
            if (categoryContainer.length) {
                categoryContainer.show();
                categoryContainer.find('.category-select').prop('disabled', false).prop('required', true);
                console.log('✓ Showing category container for:', selectedSubject);
            } else {
                console.error('✗ Category container not found for:', selectedSubject);
            }
        }
    });
    
    // ========== EDIT MODAL: Populate Data When Edit Button Clicked ==========
    $('.btn-edit').on('click', function() {
        const quizData = $(this).data('quiz');
        console.log('Edit button clicked, quiz data:', quizData);
        
        if (!quizData) {
            console.error('✗ No quiz data found');
            return;
        }
        
        // Populate form fields
        $('#edit_quiz_id').val(quizData.id);
        $('#edit-subject-select').val(quizData.subject_name);
        $('#edit_quiz_level').val(quizData.quiz_level);
        $('#edit_question').val(quizData.question);
        $('#edit_answer_a').val(quizData.answer_a);
        $('#edit_answer_b').val(quizData.answer_b);
        $('#edit_answer_c').val(quizData.answer_c);
        $('#edit_answer_d').val(quizData.answer_d);
        $('#edit_correct_answer').val(quizData.correct_answer_number);
        
        console.log('✓ Form fields populated');
        
        // Trigger subject change to show correct category dropdown
        $('#edit-subject-select').trigger('change');
        
        // Set category value after dropdown is shown (small delay to ensure DOM is ready)
        setTimeout(function() {
            const categorySelect = $('#edit-' + quizData.subject_name + '-category').find('.category-select');
            if (categorySelect.length) {
                categorySelect.val(quizData.category || '');
                console.log('✓ Category set to:', quizData.category || '(empty)');
            } else {
                console.error('✗ Category select not found for subject:', quizData.subject_name);
            }
        }, 100);
    });
    
    // ========== DELETE MODAL: Populate Data ==========
    $('.btn-delete').on('click', function() {
        const quizId = $(this).data('quiz-id');
        const subject = $(this).data('subject');
        const question = $(this).data('question');
        
        console.log('Delete button clicked:', {quizId, subject, question});
        
        $('#delete_quiz_id').val(quizId);
        $('#delete_subject_name').val(subject);
        $('#delete_question_preview').text(question);
        
        console.log('✓ Delete modal populated');
    });
    
    // ========== INITIALIZE: Show category for current subject on page load ==========
    const currentSubject = $('#add-subject-select').val();
    if (currentSubject) {
        console.log('Initializing with current subject:', currentSubject);
        $('#add-subject-select').trigger('change');
    }
    
    // ========== FORM VALIDATION: Ensure category is selected before submission ==========
    $('form').on('submit', function(e) {
        const form = $(this);
        const modal = form.closest('.modal');
        
        // Check if this is add or edit quiz form
        if (modal.attr('id') === 'addQuizModal' || modal.attr('id') === 'editQuizModal') {
            const categorySelect = modal.find('.category-select:visible:enabled');
            
            if (categorySelect.length > 0 && !categorySelect.val()) {
                e.preventDefault();
                alert('Please select a category before submitting.');
                categorySelect.focus();
                console.error('✗ Form submission blocked: No category selected');
                return false;
            }
        }
    });
    
    // ========== MODAL RESET: Clear form when modal is closed ==========
    $('#addQuizModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $(this).find('.category-container').hide();
        $(this).find('.category-select').prop('disabled', true).prop('required', false);
        console.log('✓ Add modal reset');
    });
    
    $('#editQuizModal').on('hidden.bs.modal', function() {
        $(this).find('.category-container').hide();
        $(this).find('.category-select').prop('disabled', true).prop('required', false);
        console.log('✓ Edit modal reset');
    });
    
    console.log('✓ Category Management JavaScript fully initialized');
    console.log('Available subjects:', ['english', 'math', 'filipino', 'ap', 'science']);
});
