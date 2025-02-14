import React from 'react'
import StatCard from '../../Elements/StatCard'
import { convertMinuteDecimalToHoursAndMinutes } from '../../../Helpers/date.helpers'

const PilotStats = (props) => {
  return (
    <div className="flex flex-col md:flex-row justify-start mt-2">
      <div className="md:w-1/6 my-1">
        <StatCard title="Flights" stat={props.flights} link="/logbook" />
      </div>
      <div className="md:w-1/6 my-1">
        <StatCard title="Hours" stat={props.hours > 0 ? convertMinuteDecimalToHoursAndMinutes(props.hours) : 0} link="/logbook" />
      </div>
      <div className="md:w-1/6 my-1">
        <StatCard title="Points" stat={props.points} link="/logbook" />
      </div>
      <div className="md:w-1/6 my-1">
        <StatCard title="Awards" stat={props.awards} />
      </div>
      <div className="md:w-1/6 my-1">
        <StatCard title="Location" stat={props.location} link={'/airports/' + props.location}/>
      </div>
      <div className="md:w-1/6 my-1">
        <StatCard title="Cash" stat={'$' + props.balance} link="/my-finances" />
      </div>
    </div>
  )
}

export default PilotStats
