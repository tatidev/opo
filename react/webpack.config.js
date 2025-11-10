const path = require('path');

module.exports = [
    {
        watch: true,
        entry: './src/components/portfolio/index.js',
        output: {
            path: path.resolve(__dirname, 'public/dist'),
            filename: 'Portfolio.js'
        },
        module: {
            rules: [
                {
                    exclude: /node_modules/,
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: ['@babel/preset-react'],
                            plugins: [
                                '@babel/plugin-proposal-optional-chaining',
                                '@babel/plugin-proposal-nullish-coalescing-operator'
                            ]
                        }
                    }
                },
                {
                    test: /\.css$/i,
                    use: ['style-loader', 'css-loader'],
                }
            ]
        },
        mode: 'development'
    },
    {
        watch: true,
        entry: './src/components/sourcebook/index.js',
        output: {
            path: path.resolve(__dirname, 'public/dist'),
            filename: 'Sourcebook.js'
        },
        module: {
            rules: [
                {
                    exclude: /node_modules/,
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: ['@babel/preset-react'],
                            plugins: [
                                '@babel/plugin-proposal-optional-chaining',
                                '@babel/plugin-proposal-nullish-coalescing-operator'
                            ]
                        }
                    }
                },
                {
                    test: /\.css$/i,
                    use: ['style-loader', 'css-loader'],
                }
            ]
        },
        mode: 'development'
    }
]