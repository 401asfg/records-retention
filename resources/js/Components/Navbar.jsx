// TODO: test

const Navbar = (props) => {
    return (
        <div>
            <nav className="navbar navbar-expand navbar-light bg-light p-0">
                <div className="col-4">

                </div>
                <h3 className="col-4 text-center m-2">{props.children}</h3>
                <div className="col-4">

                </div>
            </nav>
        </div>
    );
}

export default Navbar;
