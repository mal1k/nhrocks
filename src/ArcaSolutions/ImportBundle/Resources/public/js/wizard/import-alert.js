var eDirectory = eDirectory || {};
eDirectory.Import = eDirectory.Import || {};

(function () {
    var container = document.querySelector('#alerts');
    var template = document.querySelector('[data-alert]');

    eDirectory.Import.Alert = {
        add: function (message, type) {
            type = type || 'warning';

            container.style.display = 'block';
            var clone = template.cloneNode(true);

            var icon = document.createElement('i');
            icon.classList.add('fa');
            icon.classList.add('fa-exclamation-triangle');

            var messageContainer = document.createElement('p');
            messageContainer.appendChild(icon);
            messageContainer.insertAdjacentHTML('beforeend', message);

            clone.style.display = 'block';
            clone.classList.add('alert--' + type);
            clone.appendChild(messageContainer);

            container.appendChild(clone);
        },
        clear: function () {
            container.innerHTML = '';
            container.style.display = 'none';
        }
    };
})();
