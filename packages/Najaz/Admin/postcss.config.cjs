module.exports = ({ env }) => ({
  plugins: [require("tailwindcss")(), require("autoprefixer")(),require('postcss-import')()],

});
