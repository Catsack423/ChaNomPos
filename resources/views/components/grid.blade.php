<head>
    <style>
        .grid {
            display: grid;
            gap: 16px
        }

        .cols {
            grid-template-columns: 1fr;
            max-width: 70%;
            margin: 20px auto;
            padding: 0 16px;

        }

        @media (max-width: 980px) {
            .cols {
                grid-template-columns: 1fr
            }
        }

        .card {

            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 16px;

        }

        .card h2 {
            margin: 0 0 10px;
            font-size: 20px
        }

        .muted {
            color: var(--muted)
        }

        .row {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap
        }

        .spacer {
            flex: 1
        }
    </style>
</head>

<body>
    <div class="grid cols">
            {{ $slot }}
    </div>

</body>
