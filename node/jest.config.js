module.exports = {
    preset: 'ts-jest',
    testEnvironment: 'node',
    transform: {
        '^.+\\.ts$': ['ts-jest', {
            tsconfig: 'tsconfig.json',
            'ts-jest': {
                tsconfig: 'tsconfig.json'
            }
        }]
    }
};