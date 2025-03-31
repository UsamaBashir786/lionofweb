<style>

    /* Form Inputs & Select Elements Styling */

    /* Base styling for all input types */
    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="number"],
    input[type="date"],
    input[type="file"],
    select,
    textarea {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1px solid #d1d5db;
      border-radius: 0.375rem;
      background-color: #fff;
      color: #111827;
      font-size: 0.875rem;
      line-height: 1.25rem;
      transition: all 0.2s ease;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    /* Focus state */
    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus,
    input[type="number"]:focus,
    input[type="date"]:focus,
    input[type="file"]:focus,
    select:focus,
    textarea:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
    }

    /* Select dropdown specific styling */
    select {
      appearance: none;
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
      background-position: right 0.5rem center;
      background-repeat: no-repeat;
      background-size: 1.5em 1.5em;
      padding-right: 2.5rem;
    }

    /* Placeholder text color */
    ::placeholder {
      color: #9ca3af;
    }

    /* File input specific styling */
    input[type="file"] {
      padding: 0.5rem;
      cursor: pointer;
    }

    input[type="file"]::file-selector-button {
      border: 1px solid #d1d5db;
      padding: 0.25rem 0.75rem;
      border-radius: 0.25rem;
      background-color: #f3f4f6;
      margin-right: 0.75rem;
      transition: all 0.2s ease;
      cursor: pointer;
    }

    input[type="file"]::file-selector-button:hover {
      background-color: #e5e7eb;
    }

    /* Checkbox and radio styling */
    input[type="checkbox"],
    input[type="radio"] {
      width: 1rem;
      height: 1rem;
      color: #3b82f6;
      border: 1px solid #d1d5db;
      border-radius: 0.25rem;
      cursor: pointer;
    }

    input[type="checkbox"]:checked,
    input[type="radio"]:checked {
      background-color: #3b82f6;
      border-color: #3b82f6;
    }

    /* Label styling */
    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: #374151;
      font-size: 0.875rem;
    }

    /* Error state */
    .input-error {
      border-color: #ef4444 !important;
    }

    .input-error:focus {
      box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.25) !important;
    }

    /* Disabled state */
    input:disabled,
    select:disabled,
    textarea:disabled {
      background-color: #f3f4f6;
      cursor: not-allowed;
      opacity: 0.7;
    }

    /* Better padding for textareas */
    textarea {
      min-height: 6rem;
      padding: 0.75rem;
    }

    /* Specific styling for TinyMCE editor */
    .tox-tinymce {
      border: 1px solid #d1d5db !important;
      border-radius: 0.375rem !important;
    }

    /* Form groups - add spacing between form elements */
    .form-group {
      margin-bottom: 1.5rem;
    }

    /* Select Dropdown Styling */

    /* Base select styling */
    select {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1px solid #d1d5db;
      border-radius: 0.375rem;
      background-color: #fff;
      color: #111827;
      font-size: 0.875rem;
      line-height: 1.25rem;
      transition: all 0.2s ease;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);

      /* Custom dropdown arrow */
      appearance: none;
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
      background-position: right 0.5rem center;
      background-repeat: no-repeat;
      background-size: 1.5em 1.5em;
      padding-right: 2.5rem;
    }

    /* Focus state */
    select:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
    }

    /* Hover state */
    select:hover {
      border-color: #9ca3af;
    }

    /* Styling for options within the select */
    select option {
      padding: 0.75rem 1rem;
      background-color: white;
      color: #111827;
    }

    /* Hover state for options */
    select option:hover,
    select option:focus {
      background-color: #3b82f6;
      color: white;
    }

    /* Selected option */
    select option:checked {
      background-color: rgba(59, 130, 246, 0.1);
      font-weight: 500;
    }

    /* Disabled state */
    select:disabled {
      background-color: #f3f4f6;
      cursor: not-allowed;
      opacity: 0.7;
    }

    /* Option group label */
    select optgroup {
      font-weight: 600;
      color: #4b5563;
    }

    /* Firefox specific styles */
    @-moz-document url-prefix() {
      select {
        text-indent: 0.01px;
        text-overflow: '';
        padding-right: 1rem;
      }

      select::-ms-expand {
        display: none;
      }
    }

    /* Safari specific styles */
    @media screen and (-webkit-min-device-pixel-ratio:0) {
      select {
        padding-right: 2.5rem;
      }
    }

    /* Larger screens - better padding */
    @media (min-width: 640px) {
      select {
        padding: 0.75rem 1rem;
      }
    }

    select {
      padding-top: 20px !important;
      padding-left: 5px !important;
      padding-right: 5px !important;
      padding-bottom: 20px !important;
    }
</style>