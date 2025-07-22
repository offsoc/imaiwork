"use strict";const e=require("../utils/request/index.js");exports.knowledgeBaseLists=function(s){return e.request.get({url:"/knowledge/lists",data:s})};
