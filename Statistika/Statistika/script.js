document.addEventListener('DOMContentLoaded', function() {
    let selectedTechId = '';
    let selectedSubTechId = '';
    let selectedProblemTypeId = '';
    let selectedProblemLocationId = '';
    let selectedTechName = '';
    let selectedSubTechName = '';
    let selectedProblemTypeName = '';
    let selectedProblemLocationName = '';

    // Handle click on technology buttons
    document.querySelectorAll('.tech-btn').forEach(function(button) {
        button.addEventListener('click', function(event) {
            // Remove 'selected' class from all tech buttons
            document.querySelectorAll('.tech-btn').forEach(btn => btn.classList.remove('selected'));

            // Add 'selected' class to clicked button
            this.classList.add('selected');

            // Store selected technology ID and name
            selectedTechId = this.getAttribute('data-id');
            selectedTechName = this.textContent;

            // Update hidden input for technology
            document.getElementById('technology').value = selectedTechId;

            // Reset subsequent selections
            resetSelections(['sub_technology', 'problem_type', 'problem_location']);

            // Fetch and display description
            fetchDescription('technology', selectedTechId);

            // Fetch next options (sub-technologies or problem types)
            fetchNextOptions('technology', selectedTechId);
        });
    });

    function resetSelections(levels) {
        levels.forEach(level => {
            if (level === 'sub_technology') {
                selectedSubTechId = '';
                selectedSubTechName = '';
                document.getElementById('sub_technology').value = '';
                document.getElementById('sub-tech-buttons').innerHTML = '';
            } else if (level === 'problem_type') {
                selectedProblemTypeId = '';
                selectedProblemTypeName = '';
                document.getElementById('problem_type').value = '';
                document.getElementById('problem-type-buttons').innerHTML = '';
            } else if (level === 'problem_location') {
                selectedProblemLocationId = '';
                selectedProblemLocationName = '';
                document.getElementById('problem_location').value = '';
                document.getElementById('problem-location-buttons').innerHTML = '';
            }

            // Clear required indicators
            document.getElementById(level + '_required').value = '';

            // Remove 'selected' class from buttons at this level
            document.querySelectorAll('.' + level + '-btn').forEach(btn => btn.classList.remove('selected'));
        });

        // Disable the submit button
        document.getElementById('submitBtn').disabled = true;
    }

    function fetchNextOptions(currentLevel, selectedId) {
        fetch('fetch_options.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'level=' + encodeURIComponent(currentLevel) + '&id=' + encodeURIComponent(selectedId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error from server:', data.error);
                alert('Error: ' + data.error);
                return;
            }
            if (data.options && data.options.length > 0) {
                displayOptions(data.level, data.options);
                // Indicate that the next level is required
                document.getElementById(data.level + '_required').value = '1';
            } else {
                // No further options; enable the submit button
                document.getElementById('submitBtn').disabled = false;
                // Check if all required fields are selected
                checkFormValidity();
            }
        })
        .catch(error => {
            console.error('Error fetching options:', error);
            alert('Greška pri učitavanju opcija.');
        });
    }

    function displayOptions(level, options) {
        let containerId = '';
        if (level === 'sub_technology') {
            containerId = 'sub-tech-buttons';
        } else if (level === 'problem_type') {
            containerId = 'problem-type-buttons';
        } else if (level === 'problem_location') {
            containerId = 'problem-location-buttons';
        }

        const container = document.getElementById(containerId);
        container.innerHTML = ''; // Clear previous buttons

        options.forEach(option => {
            const button = document.createElement('button');
            button.textContent = option.name;
            button.classList.add('option-btn'); // Add common class
            button.classList.add(level + '-btn'); // Add specific class
            button.type = 'button';
            button.setAttribute('data-id', option.id);

            // Add event listener to buttons
            button.addEventListener('click', function(event) {
                handleSelection(event, level, option.id, option.name);
            });

            container.appendChild(button);
        });
    }

    function handleSelection(event, level, id, name) {
        // Remove 'selected' class from all buttons at this level
        document.querySelectorAll('.' + level + '-btn').forEach(btn => btn.classList.remove('selected'));

        // Add 'selected' class to the clicked button
        event.target.classList.add('selected');

        if (level === 'sub_technology') {
            selectedSubTechId = id;
            selectedSubTechName = name;
            document.getElementById('sub_technology').value = selectedSubTechId;

            // Reset subsequent selections
            resetSelections(['problem_type', 'problem_location']);

            // Fetch and display description
            fetchDescription('sub_technology', selectedSubTechId);

            // Fetch next options
            fetchNextOptions(level, selectedSubTechId);
        } else if (level === 'problem_type') {
            selectedProblemTypeId = id;
            selectedProblemTypeName = name;
            document.getElementById('problem_type').value = selectedProblemTypeId;

            resetSelections(['problem_location']);

            fetchDescription('problem_type', selectedProblemTypeId);

            // Fetch next options
            fetchNextOptions(level, selectedProblemTypeId);
        } else if (level === 'problem_location') {
            selectedProblemLocationId = id;
            selectedProblemLocationName = name;
            document.getElementById('problem_location').value = selectedProblemLocationId;

            // No further levels; enable submit button
            document.getElementById('submitBtn').disabled = false;

            fetchDescription('problem_location', selectedProblemLocationId);

            // Check if all required fields are selected
            checkFormValidity();
        }

        // Check if all required fields are selected
        checkFormValidity();
    }

    function fetchDescription(level, id) {
        fetch('fetch_opis.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'level=' + encodeURIComponent(level) + '&id=' + encodeURIComponent(id)
        })
        .then(response => response.json())
        .then(data => {
            if (data.opis) {
                displayOpis(data.name, data.opis);
            } else {
                displayOpis('', '');
            }
        })
        .catch(error => {
            console.error('Error fetching opis:', error);
        });
    }

    function displayOpis(title, opis) {
        document.getElementById('opis-title').innerText = title;
        document.getElementById('opis-content').innerHTML = opis;
    }

    function checkFormValidity() {
        const username = document.getElementById('username').value;
        const submitBtn = document.getElementById('submitBtn');

        const technologySelected = document.getElementById('technology').value;

        // Check if required indicators are set
        const subTechnologyRequired = document.getElementById('sub_technology_required').value === '1';
        const problemTypeRequired = document.getElementById('problem_type_required').value === '1';
        const problemLocationRequired = document.getElementById('problem_location_required').value === '1';

        let allRequiredFieldsSelected = username && technologySelected;

        if (subTechnologyRequired) {
            allRequiredFieldsSelected = allRequiredFieldsSelected && document.getElementById('sub_technology').value;
        }
        if (problemTypeRequired) {
            allRequiredFieldsSelected = allRequiredFieldsSelected && document.getElementById('problem_type').value;
        }
        if (problemLocationRequired) {
            allRequiredFieldsSelected = allRequiredFieldsSelected && document.getElementById('problem_location').value;
        }

        // Enable or disable the submit button
        submitBtn.disabled = !allRequiredFieldsSelected;
    }

    // Add event listener to username input
    document.getElementById('username').addEventListener('input', checkFormValidity);
});
