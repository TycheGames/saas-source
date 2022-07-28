import PropTypes from "prop-types";

import "./index.less";

const GroupTitle =  (props) => {
  return (
    <div className="group-title">
      <h1>{props.title}</h1>
    </div>
  );
};

GroupTitle.propTypes = {
    title: PropTypes.string.isRequired
}

export default GroupTitle;