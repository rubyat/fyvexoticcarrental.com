jQuery(document).ready(function($) {
    console.log("Custom checkout scripts loaded");
    
    // Don\'t set multipart/form-data as it breaks WooCommerce AJAX
    // Instead, we\'ll handle file uploads separately via AJAX
    console.log("Form enctype: " + ($("form.checkout").attr("enctype") || "Not set"));
    
    // Ensure file inputs are properly configured
    $("input[type='file']").each(function() {
        // Ensure name attribute matches id
        var inputId = $(this).attr("id");
        var inputName = $(this).attr("name");
        if (inputId && inputId !== inputName) {
            $(this).attr("name", inputId);
            console.log("Updated name attribute for: " + inputId);
        }
        console.log("File input configured: " + inputId + " (name: " + $(this).attr("name") + ")");
    });
    
    // Update debug info
    $("#form-enctype").text($("form.checkout").attr("enctype") || "Not set");
    $("#file-inputs-count").text($("input[type='file']").length);
    
    // Test AJAX connection
    $("#test-ajax-btn").on("click", function() {
        var btn = $(this);
        btn.text("Testing...").prop("disabled", true);
        
        $.ajax({
            url: checkoutData.ajaxUrl,
            type: "POST",
            data: {
                action: "test_checkout_ajax",
                nonce: checkoutData.nonce
            },
            success: function(response) {
                btn.text("Test AJAX Connection").prop("disabled", false);
                $("#ajax-test-result").html('<span style="color: green;">✓ AJAX working: ' + response.message + '</span>');
                console.log("AJAX test response:", response);
            },
            error: function(xhr, status, error) {
                btn.text("Test AJAX Connection").prop("disabled", false);
                $("#ajax-test-result").html('<span style="color: red;">✗ AJAX failed: ' + error + '</span>');
                console.error("AJAX test failed:", {xhr: xhr, status: status, error: error});
            }
        });
    });
    
    // Show selected file names
    $("input[type='file']").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".file-selected").remove(); // Remove previous selection
        if (fileName) {
            $(this).nextAll(".description").after(
                '<div class="file-selected" style="color: #007cba; font-weight: bold; margin-top: 5px;">Selected: ' + fileName + '</div>'
            );
            console.log("File selected: " + fileName + " for input: " + $(this).attr("id"));
        }
    });
    
    // File validation on client side
    $("input[type='file']").on("change", function() {
        var file = this.files[0];
        var maxSize = 2 * 1024 * 1024; // 2MB
        var allowedTypes = ["image/jpeg", "image/png", "application/pdf"];
        
        $(this).siblings(".file-error").remove(); // Remove previous errors
        
        if (file) {
            console.log("Validating file: " + file.name + ", size: " + file.size + ", type: " + file.type);
            
            if (file.size > maxSize) {
                $(this).after('<div class="file-error" style="color: #e2401c; font-weight: bold; margin-top: 5px;">File size must be under 2MB</div>');
                $(this).val("");
                return false;
            }
            
            if (allowedTypes.indexOf(file.type) === -1) {
                $(this).after('<div class="file-error" style="color: #e2401c; font-weight: bold; margin-top: 5px;">Only JPEG, PNG, and PDF files are allowed</div>');
                $(this).val("");
                return false;
            }
        }
    });
    
    // Intercept form submission to ensure files are included
    $(document.body).on("checkout_error", function(event, xhr, data) {
        console.log("Checkout error occurred");
        console.log("XHR response:", xhr);
        console.log("Data:", data);
    });
    
    // Check if all required files are uploaded before checkout
    $(document.body).on("checkout_place_order", function() {
        console.log("Checkout place order triggered");
        
        var allFilesUploaded = true;
        var missingFiles = [];
        
        $("input[type='file']").each(function() {
            var fileInput = $(this);
            if (!fileInput.data("uploaded")) {
                allFilesUploaded = false;
                missingFiles.push(fileInput.attr("name"));
            }
        });
        
        if (!allFilesUploaded) {
            console.error("Missing uploaded files:", missingFiles);
            alert("Please ensure all required documents are uploaded before proceeding with checkout.");
            return false;
        }
        
        console.log("All files uploaded, proceeding with checkout");
    });
    
    // Handle file uploads via AJAX immediately when selected
    $("input[type='file']").on("change", function() {
        var fileInput = $(this);
        var file = this.files[0];
        
        if (file) {
            // Show loading indicator
            fileInput.siblings(".file-loading").remove();
            fileInput.after('<div class="file-loading" style="color: #007cba; font-weight: bold; margin-top: 5px;">Uploading...</div>');
            
            var formData = new FormData();
            formData.append("action", "upload_checkout_files");
            formData.append("nonce", checkoutData.nonce);
            formData.append(fileInput.attr("name"), file);
            
            $.ajax({
                url: checkoutData.ajaxUrl,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    fileInput.siblings(".file-loading").remove();
                    
                    console.log("AJAX response:", response);
                    
                    if (response.success) {
                        console.log("File uploaded successfully:", response);
                        fileInput.data("uploaded", true);
                        fileInput.data("file-url", response.files[fileInput.attr("name")].url);
                        
                        // Update the hidden URL field
                        $('#' + fileInput.attr("id") + '_url').val(response.files[fileInput.attr("name")].url);
                        
                        // Show success message
                        fileInput.siblings(".file-success").remove();
                        fileInput.after('<div class="file-success" style="color: #46b450; font-weight: bold; margin-top: 5px;">✓ File uploaded successfully</div>');
                        
                        // Update debug info
                        $("#file-inputs-count").text($("input[type='file']").length + " (Uploaded: " + $("input[type='file'][data-uploaded='true']").length + ")");
                    } else {
                        console.error("File upload failed:", response.message);
                        fileInput.siblings(".file-error").remove();
                        fileInput.after('<div class="file-error" style="color: #e2401c; font-weight: bold; margin-top: 5px;">Upload failed: ' + response.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    fileInput.siblings(".file-loading").remove();
                    console.error("AJAX upload failed:", {xhr: xhr, status: status, error: error});
                    fileInput.siblings(".file-error").remove();
                    fileInput.after('<div class="file-error" style="color: #e2401c; font-weight: bold; margin-top: 5px;">Upload failed. Please try again.</div>');
                }
            });
        }
    });
});