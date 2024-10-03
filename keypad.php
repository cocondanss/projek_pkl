<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .calculator {
            width: 250px;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }
        .display {
            width: 100%;
            height: 50px;
            background-color: #6c757d;
            color: #ffffff;
            text-align: center;
            line-height: 50px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .btn {
            width: 60px;
            height: 60px;
            margin: 5px;
            font-size: 24px;
            border-radius: 10px;
        }
        .btn-number {
            background-color: #6c757d;
            color: #ffffff;
        }
        .btn-backspace {
            background-color: #dc3545;
            color: #ffffff;
        }
        .btn-enter {
            background-color: #343a40;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="calculator text-center">
        <div class="display" id="display">______</div>
        <div class="d-flex flex-wrap justify-content-center">
            <button class="btn btn-number" onclick="appendNumber('1')">1</button>
            <button class="btn btn-number" onclick="appendNumber('2')">2</button>
            <button class="btn btn-number" onclick="appendNumber('3')">3</button>
            <button class="btn btn-number" onclick="appendNumber('4')">4</button>
            <button class="btn btn-number" onclick="appendNumber('5')">5</button>
            <button class="btn btn-number" onclick="appendNumber('6')">6</button>
            <button class="btn btn-number" onclick="appendNumber('7')">7</button>
            <button class="btn btn-number" onclick="appendNumber('8')">8</button>
            <button class="btn btn-number" onclick="appendNumber('9')">9</button>
            <button class="btn btn-backspace" onclick="backspace()"> <i class="fas fa-arrow-left"></i></button>
            <button class="btn btn-number" onclick="appendNumber('0')">0</button>
            <button class="btn btn-enter" onclick="enter()"> <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
    <script>
        let pinCode = '';
        let display = document.getElementById('display');

        function appendNumber(number) {
            pinCode += number;
            display.textContent = pinCode.replace(/./g, '*');
        }

        function backspace() {
            pinCode = pinCode.slice(0, -1);
            display.textContent = pinCode.replace(/./g, '*');
        }

        function enter() {
            // TO DO: validate PIN code here
            alert('PIN code entered: ' + pinCode);
            pinCode = '';
            display.textContent = '______';
        }

        // Add event listeners for keyboard input
        document.addEventListener('keydown', function(event) {
            if (event.key >= '0' && event.key <= '9') {
                appendNumber(event.key);
            } else if (event.key === 'Backspace') {
                backspace();
            } else if (event.key === 'Enter') {
                enter();
            }
        });
    </script>
</body>
</html>